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

namespace local_scheduletool;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/attendance/locallib.php');
/**
 * Class to implement entities that can be targets of attendance marking.
 */
class modattendance_target extends target_base
{
    public \mod_attendance_structure $att_struct;
    public function __construct(object $event, $config)
    {
        parent::__construct($event, $config);
        $this->load_modattendance();
    }
    function setup_from_topic_id(string $topicId)
    {
        parent::setup_from_topic_id($topicId);

    }
    protected function load_modattendance()
    {
        global $DB;
        if ($this->cm) {
            $attendance = $DB->get_record('attendance', array('id' => $this->cm->instance), '*', MUST_EXIST);
            $this->att_struct = new \mod_attendance_structure($attendance, $this->cm, $this->course, $this->context);
        }
    }
    public function get_session()
    {
        if ($this->sessionid == null) {
            // Search compatible session by event contents.
            $sessions = $this->search_sessions();
            if (count($sessions) > 0) {
                $this->sessionid = reset($sessions)->id;
            } else {
                $this->sessionid = $this->create_session();
            }
        }

        $this->att_struct->pageparams = (object) [
            "sessionid" => $this->sessionid,
            "grouptype" => 0
        ]; // Patch attendance structure.

        return $this->att_struct->get_session_info($this->sessionid);
    }
    /**
     * Find a session with the same opening time, cmid, description, etc.
     * @return array sessions
     */
    public function search_sessions($description = null, $date = null, $time = null): array
    {
        global $DB;
        if ($date == false || $time == false) {
            // This event is not matched to an schedule.
            $opening_time = $this->event->get_opening_time();
            $description = $this->event->get_event_note();
            $params = ['attendanceid' => $this->cm->instance, 'sessdate' => $opening_time];
            $sessions = $DB->get_records('attendance_sessions', $params);
        } else {
            $opening_time = strtotime($date . ' ' . $time); // Instead of $this->event->get_opening_time() to match schedule.
            $params = [
                'attendanceid' => $this->cm->instance,
                'sessdate' => $opening_time,
                'description' => $description
            ];
            // Get session by date and description.
            $wheresql = 'attendanceid = :attendanceid AND sessdate = :sessdate AND ' . $DB->sql_compare_text('description') . ' = :description';
            $sessions = $DB->get_records_select('attendance_sessions', $wheresql, $params);
        }
        return $sessions;
    }
    /**
     * Adda new session to the attendance activity.
     * @param mixed $opening_time override the event opening time.
     * @param mixed $description override the event description.
     * @return int
     */
    public function create_session($opening_time = null, $description = null)
    {
        if ($opening_time == null) {
            $opening_time = $this->event->get_opening_time();
        }
        if ($description == null) {
            $description = $this->event->get_event_note();
        }
        $session = new \stdClass();
        $session->sessdate = $opening_time;
        $session->duration = $this->event->get_closing_time() == null ? 3600 : $this->event->get_closing_time() - $this->event->get_opening_time();
        $session->groupid = 0;
        $session->description = $description;
        $session->descriptionitemid = -1;
        $session->descriptionformat = FORMAT_PLAIN;
        $session->calendarevent = 0; // Disable calendar event creation.
        $session->timemodified = time();
        $session->statusset = 0;
        $session->absenteereport = 1;

        $session->studentscanmark = 1; // Allow students to mark their own attendance.
        $session->rotateqrcode = 1; // Use a rotating QR code.
        $session->rotateqrcodesecret = attendance_random_string();
        $session->studentpassword = attendance_random_string();

        $sessid = $this->att_struct->add_session($session);
        return $sessid;
    }
    /**
     * Check configuration and fix statuses.
     */
    public function check_configuration()
    {
        // Get statuses configured in the attendance activity.
        $statuses = $this->att_struct->get_statuses();

        // Check if lib::STATUS_DESCRIPTIONS and STATUS_ACRONYMS are configured.
        foreach (lib::STATUS_ACRONYMS as $sourcestatus => $acronym) {
            foreach ($statuses as $status) {
                if ($status->acronym == $acronym && $status->description == lib::STATUS_DESCRIPTIONS[$sourcestatus]) {
                    continue 2;
                }
            }
            // If the status is not found, add it.
            // Default value uses setnumber/attendanceid = 0.
            $newstatus = new \stdClass();
            $newstatus->attendanceid = $this->att_struct->id;
            $newstatus->acronym = $acronym;
            $newstatus->description = lib::STATUS_DESCRIPTIONS[$sourcestatus];
            $newstatus->grade = ($sourcestatus == 'NOTPRESENT') ? 0 : 1;
            $newstatus->setnumber = 0;
            $newstatus->studentavailability = ($sourcestatus == 'UNKNOWN' || $sourcestatus == 'NOTPRESENT') ? 0 : 15; // 15 minutes for manual marking.

            attendance_add_status($newstatus);
            if ($sourcestatus == 'NOTPRESENT') {
                global $DB;
                $newstatus->setunmarked = 1;
                $DB->update_record('attendance_statuses', $newstatus);
            }
        }
    }
    /**
     * Register a single attendance.
     * @param \local_scheduletool\attendance $attendance
     * @return void
     */
    public function register_attendance(attendance $attendance)
    {
        global $USER;
        $member = $attendance->get_member();
        $user = \local_scheduletool\lib::get_user_enrol($this->config, $member, $this->course);
        if (!$user) {
            if (!\local_scheduletool\lib::is_tempusers_enabled($this->config)) {
                $msg = get_string('notifications_user_unknown_notmarked', 'local_scheduletool', $member);

                lib::log_error($msg);
                $this->errors[] = $msg;
                return;
            } else {
                $tempuser = \local_scheduletool\lib::get_tempuser($attendance, $this->course);
                if (!$tempuser) {
                    $this->errors[] = $attendance;
                    return;
                }
            }
        }
        // Get the status from the list of possible statuses.
        $status = $this->get_status($attendance);
        if (!$status) {
            $this->errors[] = $attendance;
            return;
        }
        $session = $this->get_session();
        // Logs the attendance.
        $sesslog = new \stdClass();
        $sesslog->sessionid = $session->id;
        $sesslog->studentid = $user ? $user->id : $tempuser->studentid;
        $sesslog->statusid = $status->id;
        $sesslog->timetaken = $attendance->get_server_time();
        $sesslog->remarks = $attendance->get_attendance_note();
        $sesslog->takenby = $this->logtaker_user->id;
        $sesslog->statusset = null;
        // Sets the author of the log: mod_attendance uses $USER. Only needed for webservice entry point.
        $currentuser = $USER;
        $USER = $this->logtaker_user;

        $this->att_struct->save_log([$sesslog->studentid => $sesslog]);
        $USER = $currentuser;
    }
    static public function get_target_name()
    {
        return get_string('modattendance', 'local_scheduletool');
    }
    /**
     * Implement get_topics for mod_attendance.
     */
    static public function get_topics(\stdClass $user): array
    {
        global $DB;
        $topics = [];
        // Get all attendance sessions.
        $courses = get_user_capability_course('mod/attendance:addinstance', $user->id);
        if (empty($courses)) {
            return $topics;
        }
        $coursesid = array_map(function ($course) {
            return $course->id;
        }, $courses);
        // Filter allowed courses.
        $coursesid = array_filter($coursesid, function ($courseid) {
            return lib::get_course_if_allowed($courseid);
        });
        $prefix = get_config('local_scheduletool', 'restservices_prefix');
        $attendances = $DB->get_records_list('attendance', 'course', $coursesid);
        foreach ($attendances as $attendance) {
            $cm = get_coursemodule_from_instance('attendance', $attendance->id, 0, false, MUST_EXIST);
            if ($cm->idnumber === lib::CM_IDNUMBER || $cm->deletioninprogress === 1) {
                continue;
            }
            $course = lib::get_course_if_allowed($cm->course);
            if (!$course) {
                continue;
            }
            $context = \context_module::instance($cm->id);
            $att = new \mod_attendance_structure($attendance, $cm, $course, $context);
            // TODO: Get sessions by proximity in time.
            $sessions = $att->get_today_sessions();
            //$sessions = $att->get_current_sessions();
            // Each session is a topic.
            foreach ($sessions as $session) {
                // Create info text from dates.
                $description = content_to_text($session->description, FORMAT_MOODLE);
                $info = substr("{$course->fullname}: " . userdate($session->sessdate) . '(' . format_time($session->duration) . ')', 0, 100);
                $topicid = self::encode_topic_id($prefix, $cm->id, $session->id);
                $topics[] = (object) [
                    'topicId' => $topicid,
                    'name' => $att->name . " - " . $description,
                    'info' => $info, // Max 100 chars.
                    'externalIntegration' => true,
                    'tag' => substr("{$course->shortname}/{$att->name}", 0, 100), // Max 100 chars.
                    'calendar' => self::get_single_day_calendar($session, $info),
                ];
            }
        }
        return $topics;
    }
    /**
     * Create calendar structure from a single session.
     * @param \stdClass $session with sessdate, duration
     * @param string $info
     * @return object calendar structure.
     */
    static public function get_single_day_calendar(\stdClass $session, string $info): object
    {

        return (object) [
            // format: 2021-09-01
            'startDate' => date('Y-m-d', $session->sessdate),
            'endDate' => date('Y-m-d', $session->sessdate + $session->duration),
            'timetables' => [
                [
                    'weekdays' => lib::WEEK_DAYS[date('N', $session->sessdate) - 1],
                    'startTime' => date('H:i', $session->sessdate),
                    'endTime' => date('H:i', $session->sessdate + $session->duration),
                    "info" => $info, // Max 100 chars.
                ]
            ]
        ];
    }
    /**
     * Get the right status to be used.
     * 
     * @param mixed $attendance
     * @return mixed
     */
    public function get_status($attendance)
    {
        global $DB;
        $params = array('attendanceid' => $this->cm->instance, 'description' => lib::STATUS_DESCRIPTIONS[$attendance->get_mode()]);
        $statuses = $DB->get_records('attendance_statuses', $params);
        $message = count($statuses) . ' attendance statuses ' . json_encode($params) . ' found.';
        if (count($statuses) != 1) {
            lib::log_error($message);
            return false;
        } else {
            lib::log_info($message);
            return reset($statuses);
        }
    }
    /**
     * Encode topic id.
     * @param string $type
     * @param \stdClass $cm
     * @param int $sessid
     * @return array with topicid and info.
     */
    public static function encode_topic_id($calendar, $cmid, $sessionid): array
    {
        $prefix = get_config('local_scheduletool', 'restservices_prefix');
        return [$prefix . '-attendance-' . $cmid . '-' . $sessionid, $calendar->timetables[0]->info];
    }
}
