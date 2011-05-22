<?php
require('config.php');
$sql = "SELECT COUNT(*) AS rank FROM queue WHERE timestarted IS NULL AND id <= ?";
$sth = $db->prepare($sql);
$sth->execute(array((int)$_GET['id']));
$result = $sth->fetch(PDO::FETCH_ASSOC); 
$rank = $result['rank'];
$time = $rank * 5;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
            "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <link type="text/css" rel="stylesheet" href="css/styles.css" />
    </head>
    <body>

            <fieldset>
                <legend>Moodle 1.9 to 2.0 backup file converter</legend>
                <p> 
                 Your file is number <?php echo $rank ?> in the queue - we will email you a link to download your file when it has been converted in approximately <?php echo $time ?> minutes.
                </p>
                <p>&nbsp;</p>
                <p class="center"><a href="/">Upload another file...</a></p>
            </fieldset>
        </form>
    </body>
</html>
