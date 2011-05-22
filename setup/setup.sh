#!/bin/bash

DBNAME='oneninetotwo'
DBBASE='oneninetotwo_base'

# Environment checks
if [ ! -f "`which psql`" ]; then
    echo 'Could not locate psql: Please install the postgres client package'
    exit 1
fi

if [ ! -f "`which dotlockfile`" ]; then
    echo 'Could not locate dotlockfile: Please install liblockfile package'
    exit 1
fi

if [ ! -f "`which php`" ]; then
    echo 'Could not locate php: Please install the php-cli package'
    exit 1
fi

if [[ $EUID -ne 0 ]]; then
    echo "This script must be run as root" 1>&2
    exit 1
fi

read -p "Database username (oneninetotwo): " -e dbuser
if [ -z "$dbuser" ]; then
    dbuser="oneninetotwo"
fi

read -p "Database password: " -e dbpass

read -p  "Database host (localhost): " -e dbhost
if [ -z "$dbhost" ]; then
    dbhost="localhost"
fi

read -p "Database port (5432): " -e dbport
if [ -z "$dbport" ]; then
    dbport="5432"
fi

read -p "Webserver user (www-data): " -e webuser
if [ -z "$webuser" ]; then
    webuser="www-data"
fi
eval homedir=~$webuser

read -p "Data directory (/var/lib/oneninetotwo): " -e datadir
if [ -z "$datadir" ]; then
    datadir="/var/lib/oneninetotwo"
fi

read -p "Virtual Host name: " -e vhost

read -p "Admin email address: " -e adminemail

# Create a .pgpass file
if [ ! -d "$homedir" ]; then
    echo "Missing home directory: $homedir" 1>&2
    exit 1
fi


# Track if .pgpass was present before already
if [ -f "$homedir.pgpass" ]; then
    newpgpass=0
else
    newpgpass=1
fi

echo "+ Creating $homedir/.pgpass"

echo "$dbhost:$dbport:*:$dbuser:$dbpass" >> $homedir/.pgpass
chown $webuser $homedir/.pgpass
chmod 600 $homedir/.pgpass


echo "+ Creating database $DBNAME";

su $webuser -c "createdb -h $dbhost -p $dbport -U $dbuser -Eutf8 $DBNAME"

if [ $? -ne 0 ]; then
    echo
    echo "Unable to createdb: $DBNAME"
    echo
    if [[ $newpgpass -eq 1 ]]; then
        rm $homedir/.pgpass
    fi
    exit 1;
fi

echo "+ Loading schema into $DBNAME from setup/schema.sql"
# Import schema
su $webuser -c "psql -h $dbhost -p $dbport -U $dbuser $DBNAME < setup/schema.sql"

if [ $? -ne 0 ]; then
    echo
    echo "Unable to schema: setup/schema.sql"
    echo
    exit 1;
fi


# Create base moodle db
echo "+ Creating Base moodle DB: $DBBASE"
su $webuser -c "createdb -h $dbhost -p $dbport -U $dbuser -Eutf8 $DBBASE"

if [ $? -ne 0 ]; then
    echo
    echo "Unable to create Database: $DBNAME"
    echo
    exit 1;
fi


# Create datadir
echo "+ Creating directory structure"

if [ ! -d $datadir ]; then
    mkdir -p $datadir
fi

if [ ! -d "$datadir/queuein" ]; then
    mkdir $datadir/queuein
fi

if [ ! -d "$datadir/queueout" ]; then
    mkdir $datadir/queueout
fi

if [ ! -d "$datadir/backup-base" ]; then
    mkdir $datadir/backup-base
fi
chown -R $webuser $datadir

# Setup moodle config
echo "+ Generating moodle config.php";

cp setup/patches/config-19.php onenine/config.php
sed -i "s/%dbhost%/$dbhost/" onenine/config.php
sed -i "s/%dbport%/$dbport/" onenine/config.php
sed -i "s/%dbname%/$DBNAME/" onenine/config.php
sed -i "s/%dbuser%/$dbuser/" onenine/config.php
sed -i "s/%dbpass%/$dbpass/" onenine/config.php
sed -i "s|%datadir%|$datadir|" onenine/config.php

cp setup/patches/config-20.php two/config.php
sed -i "s/%dbhost%/$dbhost/" two/config.php
sed -i "s/%dbport%/$dbport/" two/config.php
sed -i "s/%dbname%/$DBNAME/" two/config.php
sed -i "s/%dbuser%/$dbuser/" two/config.php
sed -i "s/%dbpass%/$dbpass/" two/config.php
sed -i "s|%datadir%|$datadir|" two/config.php

cp www/config-dist.php www/config.php
sed -i "s/%dbhost%/$dbhost/" www/config.php
sed -i "s/%dbport%/$dbport/" www/config.php
sed -i "s/%dbname%/$DBNAME/" www/config.php
sed -i "s/%dbuser%/$dbuser/" www/config.php
sed -i "s/%dbpass%/$dbpass/" www/config.php
sed -i "s|%datadir%|$datadir|" www/config.php
sed -i "s|%vhost%|$vhost|" www/config.php
sed -i "s|%adminemail%|$adminemail|" www/config.php

cp bin/config-dist.sh bin/config.sh
sed -i "s|%datadir%|$datadir|" bin/config.sh
sed -i "s/%dbuser%/$dbuser/" bin/config.sh
sed -i "s/%dbhost%/$dbhost/" bin/config.sh
sed -i "s/%dbport%/$dbport/" bin/config.sh

# Run base moodle installADMINPW=`pwgen -s 8`
echo "+ Invoking Moodle 1.9 CLI installer"
echo "+--------------------Start 1.9 Setup----------------------+"
cd onenine
ADMINPW=`pwgen`

BACKUPID=base /usr/bin/php admin/cliupgrade.php \
      --webaddr="http://localhost" \
      --moodledir=`pwd`\
      --datadir="$datadir/backup-base" \
      --dbtype="postgres7" \
      --dbname="$DBBASE" \
      --confirmrelease=yes \
      --agreelicense=yes \
      --verbose=0 \
      --sitefullname="Moodle Test site" \
      --siteshortname="moodletest" \
      --sitesummary="This is just a test site" \
      --sitenewsitems=3 \
      --adminfirstname=Site \
      --adminlastname=Administrator \
      --adminemail=noemail@example.com \
      --adminusername=_noadmin_ \
      --adminpassword=$ADMINPW
echo
echo "+--------------------End 1.9 Setup----------------------+"
echo
echo "Finished 1.9 setup"
echo

cd ..

echo "+ Setup cronjob"
echo "*/1 * 	* * *	$webuser	dotlockfile -p -l /tmp/oneninetotwo.lock -r0 && cd `pwd`/bin && /usr/bin/php cron.php && dotlockfile -u /tmp/oneninetotwo.lock" > /etc/cron.d/oneninetotwo
echo "01  1 	* * *	$webuser	find $datadir/queuein -type f -mtime +5 -exec rm {} \;" >> /etc/cron.d/oneninetotwo
echo "30  1 	* * *	$webuser	find $datadir/queueout -type f -mtime +30 -exec rm {} \;" >> /etc/cron.d/oneninetotwo

echo
echo ++ Done!
echo
exit 0;
