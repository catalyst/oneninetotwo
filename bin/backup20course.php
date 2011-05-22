<?php
define('CLI_SCRIPT', true);

$courseid = 2;
$userid = 2;

if (!$filename = $_ENV['BACKUPDEST']) {
    die('Missing backup destination');
}


require(dirname(__FILE__).'/../two/config.php');
require($CFG->dirroot.'/backup/util/includes/backup_includes.php');

$config = get_config('backup');

$bc = new backup_controller(backup::TYPE_1COURSE, $courseid, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_AUTOMATED, $userid);

try {

    $settings = array(
            'users' => 'backup_auto_users',
            'role_assignments' => 'backup_auto_users',
            'user_files' => 'backup_auto_user_files',
            'activities' => 'backup_auto_activities',
            'blocks' => 'backup_auto_blocks',
            'filters' => 'backup_auto_filters',
            'comments' => 'backup_auto_comments',
            'completion_information' => 'backup_auto_userscompletion',
            'logs' => 'backup_auto_logs',
            'histories' => 'backup_auto_histories'
            );
    foreach ($settings as $setting => $configsetting) {
        if ($bc->get_plan()->setting_exists($setting)) {
            $bc->get_plan()->get_setting($setting)->set_value($config->{$configsetting});
        }
    }

    // Set the default filename
    $format = $bc->get_format();
    $type = $bc->get_type();
    $id = $bc->get_id();
    $users = $bc->get_plan()->get_setting('users')->get_value();
    $anonymised = $bc->get_plan()->get_setting('anonymize')->get_value();
    $bc->get_plan()->get_setting('filename')->set_value(backup_plan_dbops::get_default_backup_filename($format, $type, $id, $users, $anonymised));

    $bc->set_status(backup::STATUS_AWAITING);

    $outcome = $bc->execute_plan();
    $results = $bc->get_results();
    $file = $results['backup_destination'];

    $storage = (int)$config->backup_auto_storage;

    $outcome = $file->copy_content_to($filename);
    if ($outcome && $storage === 1) {
        $file->delete();
    }

    $outcome = true;
} catch (backup_exception $e) {
    $bc->log('backup_auto_failed_on_course', backup::LOG_WARNING, $course->shortname);
    $outcome = false;
}

$bc->destroy();
unset($bc);

?>
