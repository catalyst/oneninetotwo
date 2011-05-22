<?php

require(dirname(__FILE__).'/../www/config.php');

$sql = "SELECT *
        FROM queue
        WHERE timestarted IS NULL
        ORDER BY id";

$sth = $db->prepare($sql);
$sth->execute();

$sql = 'UPDATE queue SET timeconverted = ?, log = ? WHERE id = ?';
$sthupdate = $db->prepare($sql);

$sql = 'UPDATE queue SET timestarted = ? WHERE id = ?';
$sthstarted = $db->prepare($sql);

while($task = $sth->fetch(PDO::FETCH_OBJ)) {
    $sthstarted->execute(array(time(), $task->id));

    $file = DIRIN.'/'.$task->id.'-'.$task->filename;
    $dest = DIROUT .'/'.$task->id.'-'.preg_replace('/\.zip$/i', '.mbz', $task->filename);

    if (is_file($file)) {
        $cmd = "BACKUPID={$task->id} BACKUPFILE=\"{$file}\" BACKUPDEST=\"{$dest}\" ./runupgrade";
    }
    echo $cmd;
    $log = `$cmd`;
    echo $log;

    $sthupdate->execute(array(time(), $log, $task->id));

    if (is_file($dest)) {
        $link    = DOWNLOADLINK.'?id='.$task->id."&hash=".sha1_file($dest);
        $headers = 'From: '.ADMINEMAIL."\r\n".
                   'Reply-To: '.ADMINEMAIL."\r\n".
                   'X-Mailer: PHP/' . phpversion();
        $subject = '[Moodle 2 Converter] '.$task->filename.' ready for download';
        $body    = "Your Moodle 2 backup file is ready to download:

$link



NOTE: This file will be available for 30 days, after this time it will be deleted.

";

        mail($task->email, $subject, $body, $headers);
    } else {
        mail(ADMINEMAIL, 'Unable to convert moodle backup', print_r($task, true));
    }

}

