<?php
require('config.php');

var_dump($_FILES);
if (empty($_POST['email']) || empty($_FILES['backupfile'])) {
    header('Location: /');
    exit;
}

$db->beginTransaction();

$sql = "INSERT INTO queue (email, filename, timesubmitted) values(?, ?, ?) RETURNING id";

$sth = $db->prepare($sql);
$sth->execute(array($_POST['email'], $_FILES['backupfile']['name'], time()));

$result = $sth->fetch(PDO::FETCH_ASSOC); 

$backupid = $result['id'];

$infile = DIRIN.'/'.$backupid.'-'.$_FILES['backupfile']['name'];

if(validate_backup($_FILES['backupfile']['tmp_name'])) {
    move_uploaded_file($_FILES['backupfile']['tmp_name'], $infile);

    $db->commit();

    header('Location: confirmation.php?id='.$backupid);
} else {
    $db->rollback();
    header('Location: invalid.php');
}


function validate_backup($path) {
    if (!$zh = zip_open($path)) {
        return false;
    }
    while ($entry = zip_read($zh)) {
        if (zip_entry_name($entry) == 'moodle.xml') {
            zip_close($zh);
            return true;
        }
    }
    zip_close($zh);
    return false;
}

?>
