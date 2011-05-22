<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
            "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <link type="text/css" rel="stylesheet" href="css/styles.css" />
    </head>
    <body>

        <form method="post" enctype="multipart/form-data" action="backup_upload.php">
            <fieldset>
                <legend>Moodle 1.9 to 2.0 backup file converter</legend>
                <p>
                    <label for="bakupfile">Moodle 1.9 backup</label>
                    <input type="file" name="backupfile" id="backupfile" />
                </p>
                <p>
                    <label for="email">Your email address</label>
                    <input type="text" name="email" id="email" size="40" maxlength="255" />
                </p>
                <p class="submit">
                    <input type="submit" value="Convert it" />
                </p>
            </fieldset>
        </form>
    </body>
</html>
