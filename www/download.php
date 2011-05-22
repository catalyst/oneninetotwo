<?php

require('config.php');

$id   = (int)$_GET['id'];
$hash = $_GET['hash'];

try {

    $sql = "SELECT * FROM queue WHERE id = ?";
    $sth = $db->prepare($sql);

    $sth->execute(array($id));
    $task = $sth->fetch(PDO::FETCH_OBJ);

    $filename =  DIROUT .'/'.$task->id.'-'.preg_replace('/\.zip$/i', '.mbz', $task->filename);

    if (sha1_file($filename) != $hash) {
        die('Unable to find your file sorry');
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($filename));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: private');
    header('Content-Length: ' . filesize($filename));
    ob_clean();
    flush();
    readfile($filename);
    exit;

} catch (PDOException $e) {
    echo 'Unable to find your file sorry';
    exit;
}

?>
