<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Add_sessions to attendance.
 *
 * @package    local_scheduletool
 * @copyright  2024 University of Valladoild, Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use local_scheduletool\course_target;
use local_scheduletool\modattendance_target;
use local_scheduletool\target_base;

require_once(__DIR__ . '/../../config.php');

// Shows a form to select date range to import sessions.


// Params.
$cmid = optional_param('cmid', null, PARAM_INT);
$courseid = optional_param('course', null, PARAM_INT);

if ($cmid) {
    [$course, $cm] = get_course_and_cm_from_cmid($cmid, 'attendance');
    $context = context_module::instance($cm->id);
    $description = get_string('copy_schedule_description', 'local_scheduletool', $cm->name);
} else {
    $course = get_course($courseid);
    $cm = null;
    $context = context_course::instance($course->id);
    $description = get_string(
        'copy_shedule_course',
        'local_scheduletool',
        ['coursename' => $course->fullname, 'attendancename' => get_config('local_scheduletool', 'module_name')]
    );
}

require_course_login($course, true, $cm);
require_capability('mod/attendance:addinstance', $context);

$PAGE->set_url(new moodle_url('/local/scheduletool/add_sessions.php', ['cmid' => $cmid, 'course' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('copy_schedule', 'local_scheduletool'));
$PAGE->set_heading(get_string('copy_schedule', 'local_scheduletool'));
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();
echo $OUTPUT->notification($description, 'info', false);

// Check copy_schedule_enabled setting.
$copy_schedule_enabled = get_config('local_scheduletool', 'copy_schedule_enabled');
if (!$copy_schedule_enabled) {
    echo $OUTPUT->notification(get_string('copy_schedule_disabled', 'local_scheduletool'), 'error');
    echo $OUTPUT->footer();
    die();
}
$fromdate = optional_param_array('fromdate', null, PARAM_RAW);
$todate = optional_param_array('todate', null, PARAM_RAW);

$form = new \local_scheduletool\forms\add_sessions_form(
    null,
    [
        'cmid' => $cmid,
        'course' => $course,
        'compress' => optional_param('compress', false, PARAM_BOOL),
        'fromdate' => $fromdate,
        'todate' => $todate
    ]
);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/attendance/view.php', ['id' => $cm->id]));
} else if ($data = $form->get_data()) {
    // Check action: Refresh or add.
    if (isset($data->refresh)) {
        $form->display();
    } else {
        // Extract selected calendars.
        $calendars = [];
        $sessioncount = 0;
        foreach ($data as $key => $value) {
            if (strlen($key) > 50 && $value == "1") {
                $calendar = json_decode(base64_decode($key));

                // If instance has static idnumber set use course_target else attendance_target.
                if ($cm == null || ($cm->idnumber === local_scheduletool\lib::CM_IDNUMBER && $cm->deletioninprogress === 0)) {
                    list($topicid) = course_target::encode_topic_id($calendar, $course);
                } else {
                    // Null sesion
                    list($topicid) = modattendance_target::encode_topic_id($calendar, $cm->id, null);
                }
                $calendars[] = $calendar;


                $startweekday = $calendar->timetables[0]->weekdays;
                $dates = local_scheduletool\lib::expand_dates_from_calendar($calendar);
                foreach ($dates as $date) {
                    // Format $calendar-> "2024-08-21T03:52:19+0000"
                    // Create a mock event.
                    $openingDate = $date->date;
                    $closingDate = $date->date;
                    $openingTime = $date->startTime;
                    $closingTime = $date->endTime;
                    $eventOpeningTime = date('c', strtotime($openingDate . ' ' . $openingTime));
                    $eventClosingTime = date('c', strtotime($closingDate . ' ' . $closingTime));
                    $useridfield = get_config('local_scheduletool', 'user_id');
                    $eventobj = (object) [
                        'topic' => (object) [
                            'topicId' => $topicid,
                            'name' => '',
                            'type' => 'D',
                            'member' => (object) [
                                'username' => $USER->$useridfield,
                                'firstname' => $USER->firstname,
                                'lastname' => $USER->lastname,
                                'email' => $USER->email
                            ],
                        ],
                        //  format $calendar-> "2024-08-21T03:52:19+0000"
                        'openingTime' => $eventOpeningTime,
                        'closingTime' => $eventClosingTime,
                        'eventNote' => $date->info,
                        'attendances' => []
                    ];
                    $event = new local_scheduletool\event($eventobj);
                    $att_target = target_base::get_target($event, get_config('local_scheduletool'));
                    // Force to get session.
                    $session = $att_target->get_session();
                    if ($session) {
                        $sessioncount++;
                    }
                }
            }
        }
        echo $OUTPUT->notification(get_string('count_sessions_added', 'local_scheduletool', $sessioncount));
    }

    // echo $OUTPUT->continue(new moodle_url('/mod/attendance/view.php', ['id' => $cm->id]));
} else {
    $form->display();
}

echo $OUTPUT->footer();
