<?php
define('DSN', 'pgsql:dbname=%dbname%;host=%dbhost%;port=%dbport%');
define('DBUSER', '%dbuser%');
define('DBPASS', '%dbpass%');
define('DIRIN', '%datadir%/queuein');
define('DIROUT', '%datadir%/queueout');
define('DOWNLOADLINK', 'http://%vhost%/download.php');
define('ADMINEMAIL', '%adminemail%');

ini_set('memory_limit', '512M');

try {
    $db = new PDO(DSN, DBUSER, DBPASS);
} catch (PDOException $e) {
    echo $e->getMessage();
    exit;
}
?>
