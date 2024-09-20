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
function local_attendancewebhook_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context) {
    // if (get_config('local_notificationsagent', 'disable_user_use')) {
    //     if (!has_capability('local/notificationsagent:managecourserule', $context)) {
    //         return;
    //     }
    // }

    // $menuentrytext = 'Horarios';
    // $courseid = $course->id;
    // $url = '/local/attendancewebhook/index.php?courseid=' . $courseid;
    // $parentnode->add(
    //     $menuentrytext,
    //     new moodle_url($url),
    //     navigation_node::TYPE_SETTING,
    //     null,
    //     "attendancewebhook"
    // );
    // // Add report navigation node.
    // $reportnode = $parentnode->get('coursereports');
    // if (isset($reportnode) && $reportnode !== false) {
    //     $reporturl = '/local/notificationsagent/report.php?courseid=' . $courseid;
    //     $reportnode->add(
    //         get_string('pluginname', 'local_notificationsagent'),
    //         new moodle_url($reporturl),
    //         navigation_node::TYPE_SETTING
    //     );
    // }
}