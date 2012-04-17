<?php
require(dirname(__FILE__).'/../onenine/config.php');
require_once($CFG->dirroot.'/backup/restorelib.php');
require_once($CFG->dirroot.'/backup/lib.php');

if (!isset($_ENV['BACKUPFILE'])) {
    die('No backup file');
}

if (!is_file($_ENV['BACKUPFILE'])) {
    die ('Not a file');
}

$preferences = array('restore_groups' => true,
                     'restore_logs' => true,
                     'restore_messages' => true,
                     'restore_blogs' => true,
                     'restore_course_files' => true,
                     'restore_site_files' => true,
                     'restore_metacourse' => true);

import_backup_file_silently($_ENV['BACKUPFILE'], 0, true, true,$preferences);
