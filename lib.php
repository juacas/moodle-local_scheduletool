<?php

/**
 *
 * Menu element
 *
 * @param navigation_node $parentnode
 * @param stdClass $course
 * @param context_course $context
 *
 * @return void
 */
function local_attendancewebhook_extend_settings_navigation(settings_navigation $settings, $context) {
    // Check copy_schedule_enabled setting.
    $copy_schedule_enabled = get_config('local_attendancewebhook', 'copy_schedule_enabled');
    if (!$copy_schedule_enabled) {
        return;
    }
    // Check if its a course context.
    if ($context instanceof core\context\course) {
        $menuentrytext = get_string('copy_schedule', 'local_attendancewebhook');
        $url = new \moodle_url('/local/attendancewebhook/add_sessions.php',
            ['course' => $context->instanceid]);
        $modulesettings = $settings->get('courseadmin');
        if ($modulesettings) {
            $modulesettings->add($menuentrytext, $url, 
                    navigation_node::TYPE_CUSTOM, null, 'attendance_hook');
        }

        return;
    }
    // Check activity type.
    $id = $context->instanceid;
    $cm = get_coursemodule_from_id('attendance', $id);
    if (!$cm) {
        return;
    }
    // Check capabilities.
    if (!has_capability('mod/attendance:addinstance', $context)) {
        return;
    }
    $menuentrytext = get_string('copy_schedule', 'local_attendancewebhook');
    $url = new \moodle_url('/local/attendancewebhook/add_sessions.php',
        ['cmid' => $context->instanceid]);
    $modulesettings = $settings->get('modulesettings');
    if ($modulesettings) {
        $modulesettings->add($menuentrytext, $url, 
                navigation_node::TYPE_CUSTOM, null, 'attendance_hook');
    }
    

}