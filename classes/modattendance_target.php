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

namespace local_attendancewebhook;

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . '/mod/attendance/locallib.php');
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
        $this->att_struct->pageparams = (object) ["sessionid" => $this->sessionid]; // Patch attendance structure.
        return $this->att_struct->get_session_info($this->sessionid);
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
            $newstatus->grade = 1;
            $newstatus->setnumber = 0;
            attendance_add_status($newstatus);
        }
    }
    /**
     * Register a single attendance.
     * @param \local_attendancewebhook\attendance $attendance
     * @return void
     */
    public function register_attendance(attendance $attendance)
    {
        global $USER;
        $member = $attendance->get_member();
        $user = \local_attendancewebhook\lib::get_user_enrol($this->config, $member, $this->course);
        if (!$user) {
            if (!\local_attendancewebhook\lib::is_tempusers_enabled($this->config)) {
                $msg = 'User unknown not marked: ' . $member;
                lib::log_error($msg);
                $this->errors[] = $msg;
                return;
            } else {
                $tempuser = \local_attendancewebhook\lib::get_tempuser($attendance, $this->course);
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
        // Sets the author of the log: mod_attendance uses $USER.
        $currentuser = $USER;
        $USER = $this->logtaker_user;

        $this->att_struct->save_log([$sesslog]);
        $USER = $currentuser;
    }
    static public function get_target_name()
    {
        return get_string('modattendance', 'local_attendancewebhook');
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
        $attendances = $DB->get_records_list('attendance', 'course', $coursesid);
        foreach ($attendances as $attendance) {
            $cm = get_coursemodule_from_instance('attendance', $attendance->id, 0, false, MUST_EXIST);
            $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
            $context = \context_module::instance($cm->id);
            $att = new \mod_attendance_structure($attendance, $cm, $course, $context);
            // TODO: Get sessions by proximity in time.
            $sessions = $att->get_today_sessions();
            //$sessions = $att->get_current_sessions();
            // Each session is a topic.
            foreach ($sessions as $session) {
                // Create info text from dates.
                $description = content_to_text($session->description, FORMAT_MOODLE);
                $info = "{$course->fullname}: " . userdate($session->sessdate) . '(' . format_time($session->duration) . ')';
                $topics[] = [
                    'topicId' => 'attendance-' . $cm->id . '-' . $session->id,
                    'name' => $course->shortname . ":" . $att->name . " " . $description,
                    'info' => $info,
                    'externalIntegration' => true,
                    'tag' => "{$course->shortname}/{$att->name}/{$description}/{$info}",
                ];
            }
        }
        return $topics;
    }
    /**
     * Get the right status to be used.
     * 
     * @param mixed $attendance
     * @return mixed
     */
    public function get_status( $attendance) {
        global $DB;
        $params = array('attendanceid' => $this->cm->instance, 'description' => lib::STATUS_DESCRIPTIONS[$attendance->get_mode()]);
        $statuses = $DB->get_records('attendance_statuses', $params);
        $message = count($statuses).' attendance statuses '.json_encode($params).' found.';
        if (count($statuses) != 1) {
            lib::log_error($message);
            return false;
        } else {
            lib::log_info($message);
            return reset($statuses);
        }
    }

}
