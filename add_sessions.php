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
 * @package    local_attendancewebhook
 * @copyright  2024 University of Valladoild, Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use local_attendancewebhook\course_target;
use local_attendancewebhook\modattendance_target;
use local_attendancewebhook\target_base;

require_once(__DIR__ . '/../../config.php');

// Shows a form to select date range to import sessions.


// Params.
$cmid = required_param('cmid', PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'attendance');
require_course_login($course, true, $cm);
$context = context_module::instance($cmid);
require_capability('mod/attendance:addinstance', $context);

$PAGE->set_url(new moodle_url('/local/attendancewebhook/add_sessions.php', ['cmid' => $cmid]));
//$PAGE->set_context($context);
$PAGE->set_title(get_string('copy_schedule', 'local_attendancewebhook'));
$PAGE->set_heading(get_string('copy_schedule', 'local_attendancewebhook'));
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('copy_schedule_description', 'local_attendancewebhook'));

// Check copy_schedule_enabled setting.
$copy_schedule_enabled = get_config('local_attendancewebhook', 'copy_schedule_enabled');
if (!$copy_schedule_enabled) {
    echo $OUTPUT->notification(get_string('copy_schedule_disabled', 'local_attendancewebhook'), 'error');
    echo $OUTPUT->footer();
    die();
}

$form = new \local_attendancewebhook\forms\add_sessions_form(null, ['cmid' => $cmid, 'course' => $course]);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/attendance/view.php', ['id' => $cm->id]));
} else if ($data = $form->get_data()) {
    // Extract selected calendars.
    $calendars = [];
    foreach ($data as $key => $value) {
        if (strlen($key) > 50 && $value == "1") {
            $calendar = json_decode(base64_decode($key));

            // If instance has static idnumber set use course_target else attendance_target.
            if ($cm->idnumber === local_attendancewebhook\lib::CM_IDNUMBER && $cm->deletioninprogress === 0) {
                list($topicid) = course_target::encode_topic_id($calendar, $course);
            } else {
                // Null sesion
                list($topicid) = modattendance_target::encode_topic_id($calendar, $cm->id, null);
            }
            $calendars[] = $calendar;


            $startweekday = $calendar->timetables[0]->weekdays;
            $dates = local_attendancewebhook\lib::expand_dates_from_calendar($calendar);
            foreach ($dates as $date) {
                // Format $calendar-> "2024-08-21T03:52:19+0000"
                // Create a mock event.
                $openingDate = $date->date;
                $closingDate = $date->date;
                $openingTime = $date->startTime;
                $closingTime = $date->endTime;
                $eventOpeningTime = date('c', strtotime($openingDate . ' ' . $openingTime));
                $eventClosingTime = date('c', strtotime($closingDate . ' ' . $closingTime));

                $eventobj = (object) [
                    'topic' => (object) [
                        'topicId' => $topicid,
                        'name' => '',
                        'type' => 'D',
                        'member' => (object) [
                            'username' => $USER->username, // TODO: use configured user field.
                            'firstname' => $USER->firstname,
                            'lastname' => $USER->lastname,
                            'email' => $USER->email
                        ],
                    ],
                    //  format $calendar-> "2024-08-21T03:52:19+0000"
                    'openingTime' => $eventOpeningTime,
                    'closingTime' => $eventClosingTime,
                    'eventNote' => $calendar->timetables[0]->info,
                    'attendances' => []
                ];
                $event = new local_attendancewebhook\event($eventobj);
                $att_target = target_base::get_target($event, get_config('local_attendancewebhook'));
                // Force to get session.
                $session = $att_target->get_session();
            }
        }

    }

    $response = count($calendars) > 0 ? 1 : 0;
    //$response = \local_attendancewebhook\lib::process_add_session($data);
    if ($response == 1) {
        echo $OUTPUT->notification(get_string('sessions_added', 'local_attendancewebhook'));
    } else {
        echo $OUTPUT->notification(get_string('error_adding_sessions', 'local_attendancewebhook'), 'error');
    }
    echo $OUTPUT->continue(new moodle_url('/mod/attendance/view.php', ['id' => $cm->id]));
} else {
    $form->display();
}

echo $OUTPUT->footer();
