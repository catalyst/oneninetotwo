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


$course = new stdClass();
$course->category = 1;
$course->fullname = 'Fake.1';
$course->shortname = 'Fake.1';
$course->idnumber = 'Fake.1';
$course->format = 'weeks';
$course->numsections = 60;


$preferences = array('restore_groups' => true,
                     'restore_logs' => true,
                     'restore_messages' => true,
                     'restore_blogs' => true,
                     'restore_course_files' => true,
                     'restore_site_files' => true,
                     'restore_metacourse' => true);

if ($destcourse = create_course($course)) {
    import_backup_file_silently($_ENV['BACKUPFILE'], $destcourse->id, true, true,$preferences);
}

// Sync the course to the backup file
// HACK - peek at the info moodle backup restore keeps in the session

$course = $destcourse;
$course->fullname       = $SESSION->course_header->course_fullname;
$course->summary        = $SESSION->course_header->course_summary;
$course->idnumber       = $SESSION->course_header->course_idnumber;
$course->shortname      = $SESSION->course_header->course_shortname;
$course->numsections    = $SESSION->course_header->course_numsections;
$course->format         = $SESSION->course_header->course_format;

update_course(addslashes_object($course));
?>
