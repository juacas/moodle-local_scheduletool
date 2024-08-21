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
use \mod_hybridteaching\controller\sessions_controller;
use \mod_hybridteaching\controller\attendance_controller;
defined('MOODLE_INTERNAL') || die();
require_once ($CFG->dirroot . '/mod/hybridteaching/lib.php');
/**
 * Class to implement entities that can be targets of attendance marking.
 */
class modhybridteaching_target extends target_base
{
    public sessions_controller $sessioncontroller;
    public attendance_controller $attendancecontroller;
    public \stdClass $hybridteaching;

    /**
     * Constructor.
     * @param event $event
     * @param \stdClass $config
     */
    public function __construct(object $event, $config)
    {
        parent::__construct($event, $config);
        $this->load_modhybridteaching();
    }
    protected function load_modhybridteaching()
    {
        global $DB;
        if ($this->cm) {
            $hybridteaching = $DB->get_record('hybridteaching', array('id' => $this->cm->instance), '*', MUST_EXIST);
            $this->sessioncontroller = new sessions_controller($hybridteaching);
            $this->attendancecontroller =  new attendance_controller($hybridteaching);
            $this->hybridteaching = $hybridteaching;
        }
    }
    public function get_session()
    {
        return $this->sessioncontroller->get_session($this->sessionid);
    }
    /**
     * Check configuration of hybridteaching and fix statuses.
     */
    public function check_configuration()
    {
        // Get statuses configured in the attendance activity.
       // TODO: Conditions?
    }
    /**
     * Register a single attendance.
     * @param \local_attendancewebhook\attendance $attendance
     * @return void
     */
    public function register_attendance(attendance $attendance)
    {
        global $USER;
        $user = \local_attendancewebhook\lib::get_user_enrol($this->config, $attendance->get_member(), $this->course);
        // TODO: Supports temp users???
        if (!$user) {
           throw new \Exception('User not found: ' . $attendance );
        }
        try {
        // Mark the attendance to the session.
        $currentuser = $USER;
        $USER = $this->logtaker_user;
        $this->set_attendance_status($user, HYBRIDTEACHING_ATTSTATUS_VALID);
        $USER = $currentuser;
        } catch (\Exception $e) {
            lib::log_error($e);
            $this->errors[] = $e->getMessage();
        }
        return $log;
    }
    /**
     * 
     * TODO: Refactor in hybridteaching
     * @return mixed
     */
    private function set_attendance_status($user, $status) {
            global $DB, $CFG, $USER;
            $session = $this->get_session();
            $attendance = attendance_controller::hybridteaching_get_attendance($session, $user->id);
            $timemodified = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
            if ($attendance) {
                $attendance->status = $status;
                $attendance->usermodified = $USER->id;
                $attendance->timemodified = $timemodified;
                $status ? $attendance->connectiontime = $session->duration : $attendance->connectiontime = 0;
                $DB->update_record('hybridteaching_attendance', $attendance);
    
                list($course, $cm) = get_course_and_cm_from_instance($attendance->hybridteachingid, 'hybridteaching');
                $event = \mod_hybridteaching\event\attendance_updated::create([
                    'objectid' => $attendance->hybridteachingid,
                    'context' => \context_module::instance($cm->id),
                    'relateduserid' => $attendance->userid,
                    'other' => [
                        'sessid' => $attendance->sessionid,
                        'attid' => $attendance->id,
                    ],
                ]);
    
                $event->trigger();
    
                // Update completion state.
                $hybridteaching = $DB->get_record('hybridteaching', ['id' => $attendance->hybridteachingid]);
                $cm = $this->cm;
    
                $completion = new \completion_info($course);
                if ($completion->is_enabled($cm) && $hybridteaching->completionattendance) {
                    $status ? $completion->update_state($cm, COMPLETION_COMPLETE, $attendance->userid) :
                        $completion->update_state($cm, COMPLETION_INCOMPLETE, $attendance->userid);
                }
            }
            return $attendance;
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
        $courses = get_user_capability_course('mod/hybridteaching:addinstance', $user->id);
        if (empty($courses)) {
            return $topics;
        }
        $coursesid = array_map(function ($course) {
            return $course->id;
        }, $courses);
        $prefix = get_config('local_attendancewebhook', 'restservices_prefix');
        $hybridteachings = $DB->get_records_list('hybridteaching', 'course', $coursesid);
        foreach ($hybridteachings as $hybridteaching) {
            $cm = get_coursemodule_from_instance('hybridteaching', $hybridteaching->id, 0, false, MUST_EXIST);
            $course = lib::is_course_allowed($cm->course);
            if (!$course) {
                continue;
            }
            $sessions = [];
            
            $sessionsinprogress = sessions_controller::get_sessions_in_progress($hybridteaching->id);
            if (count($sessionsinprogress) == 0) {
                $hybridsessioncontrol = new sessions_controller($hybridteaching);
                $sessionnext = $hybridsessioncontrol->get_next_session();
                if ($sessionnext) {
                    $sessions = array_merge([$sessionnext], $sessions);
                }
            } else {
                $sessions = $sessionsinprogress;
            }

            // Each session is a topic.
            foreach ($sessions as $session) {
                // Create info text from dates.
                $description = content_to_text($session->description, FORMAT_MOODLE);
                $info = "{$course->fullname}: " . userdate($session->starttime) . '(' . format_time($session->duration) . ')';
                $topics[] = (object) [
                    'topicId' => $prefix . '-hybridteaching-' . $cm->id . '-' . $session->id,
                    'name' => $course->shortname . ":" . $hybridteaching->name . " " . $description,
                    'info' => $info,
                    'externalIntegration' => true,
                    'tag' => "{$course->shortname}/{$hybridteaching->name}/{$description}/{$info}",
                ];
            }
        }
        return $topics;
    }
}
