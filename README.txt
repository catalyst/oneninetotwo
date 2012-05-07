=======================================================================
 Automated Moodle 1.9 to 2.0 course conversion
=======================================================================

This converter is a collection of scripts that Automate the process of
importing a Moodle 1.9 backup file, upgrading the entire Moodle site to
2.0 and then exporting a Moodle 2.0 format backup file.

This development work was made possible through funding from the New
Zealand Ministry of Education, and was quickly put together to assist
teachers affected by the Christchurch earthquake in sharing Moodle
courses with other Schools.


=======================================================================
 Prerequisites
=======================================================================

 - Linux server that meets the Moodle 2.0 system requirements
 - PostgreSQL   - http://www.postgresql.org/
 - Git          - http://git-scm.com/
 - dotlockfile  - http://www.linuxmanpages.com/man1/dotlockfile.1.php
 - Ensure that the php.ini file used by your command-line PHP executable
has the "variables_order" flag set to include "E"



=======================================================================
 Installation
=======================================================================

 1. Clone git repository

    $ cd /var/www  # or another location of your choosing
    $ git clone git://github.com/matt-catalyst/oneninetotwo.git oneninetotwo && cd oneninetotwo


 2. Download Moodle 1.9.x and 2.0.x repositories (may take some time)

    $ setup/init.sh

    NOTE: If you have any non-standard modules used in your Moodle 1.9 backup
    files then you must add the 1.9 and 2.0 module code to the  ./onenine
    and ./two directories. The setup script in step 4 will initiate
    the database installation of your modules, If your modules
    require any additional setup then you will need to configure your
    webserver to point to the ./onenine directory and then login with
    your browser and complete the setup.

 3. Create Postgres DB user: (with createdb permissions)

    $ createuser -SdRP oneninetotwo


 4. Run installation script

    $ sudo ./setup/setup.sh

    Enter the following when prompted:

        - Database username:
            As setup in step 3. e.g. oneninetotwo

        - Database password:
            As setup in step 3. e.g. ohmohPh9

        - Database host:
            e.g. localhost

        - Database port:
            Default port is 5432

        - Webserver user:
            Normally www-data, apache or httpd

        - Data directory:
            Directory to be used for all Moodle data files (will be created by script) e.g. /var/lib/sitedata/oneninetotwo

        - Virtual Host name:
            Domain you intend to run the converter from e.g. oneninetotwo.catalyst.net.nz

        - Admin email address:
            Email address that users can reply to when using this converter

5. Configure your web-server to serve the content from the www directory

    e.g.

    <VirtualHost *:80>
        ServerName oneninetwo.catalyst.net.nz
        DocumentRoot /var/www/oneninetotwo/www
    </VirtualHost>



