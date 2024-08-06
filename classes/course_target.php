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
/**
 * Course target refers only a course.
 * It creates a new attendance activity and registers new sessions.
 */
class course_target extends modattendance_target
{
    public \mod_attendance_structure $att_struct;
    public function __construct(object $event, $config)
    {
        global $DB;
        parent::__construct($event, $config);
    }
    function parse_topic_id(string $topicId) {
        global $DB;
        $topicparts = explode('-', $topicId);
        if (count($topicparts) != 2) {
           throw new \Exception("Invalid topicId format: {$topicId}");
        }
        $courseid = $topicparts[1];
        $this->course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    }
    /**
     * Search a session with the same opening time. If not found, create a new one.
     * If there are more than one session with the same opening time, log an error.
     * @return mixed session structure or false if not found.
     */
    public function get_session()
    {
        // Check configuration and fix statuses.
        $this->check_configuration();
        // Find a session with the same opening time.
        global $DB;
        $params = ['attendanceid' => $this->cm->instance, 'sessdate' => $this->event->get_opening_time()];
        $sessions = $DB->get_records('attendance_sessions', $params);
        $message = count($sessions).' attendance session(s) '.json_encode($params).' found.';
        if (count($sessions) > 1) {
            $this->errors[] = $message;
            lib::log_error($message);
            return false;
        } else {
            lib::log_info($message);
            if (count($sessions) == 1) {
                $session = reset($sessions);
                $this->sessionid = $session->id;
                lib::log_info('Attendance session updated.');
            } else {
                $session = new \stdClass();
                $session->sessdate = $this->event->get_opening_time();
                $session->duration = $this->event->get_closing_time() - $this->event->get_opening_time();
                $session->description = $this->event->get_event_note();
                $session->groupid = 0;

                $sessid = $this->att_struct->add_session($session);
                $this->sessionid = $sessid;
                
                lib::log_info('Attendance session created.');
            }
        }
        return parent::get_session();
    }
    /**
     * Check configuration and fix statuses, names and section position.
     * Creates a new attendance activity if not found.
     */
    public function check_configuration()
    {
        // Checks if the attendance activity is instanced in course.
        // Search attendance module in course with idnumber = self::CM_IDNUMBER using cached cm_modinfo.
        $cminfo = get_fast_modinfo($this->course);
        $cms = $cminfo->get_instances_of('attendance');
        // Filter out the instance in deletion.
        $cms = array_filter($cms, function ($cm) {
            return $cm->idnumber === lib::CM_IDNUMBER && $cm->deletioninprogress === 0;
        });
        $creating_new = count($cms) == 0;
        if ($creating_new) {
            // Create a new attendance activity.
            $moduleinfo = new \stdClass();
            $moduleinfo->course = $this->course->id;
            $moduleinfo->modulename = 'attendance';
            $moduleinfo->section = $this->config->module_section;
            $moduleinfo->visible = 1;
            $moduleinfo->introeditor = ['idnumber' => 0, 'text' => '', 'format' => FORMAT_PLAIN];
            $moduleinfo->cmidnumber = lib::CM_IDNUMBER;
            $moduleinfo->name = $this->config->module_name;
            $cm = create_module($moduleinfo);
        } else {
            $cm = reset($cms);
        }
        $this->set_cm($cm);
        
        // Initialice the class info.
        $this->load_modattendance();

        // Remove dafault statuses silently. Attendance API trigger events.
        if ($creating_new) {
            global $DB;
            $DB->delete_records('attendance_statuses', ['attendanceid' => $this->cm->instance]);
        }
        // Ensure desired statuses are configured in the attendance activity.
        parent::check_configuration();

        // Check name and section restrictions. Why?
        // Force the name. Why?
        if ($this->att_struct->name != $this->config->module_name) { // TODO: Why name is restored?
            lib::log_info('Module name modified.');
            set_coursemodule_name($this->cm->id, $this->config->module_name);
            lib::log_info('Module name updated.');
        }
        // Force to be in section $config->module_section.        
        if ( $this->config->module_section != $cm->sectionnum) {
            lib::log_info('Section number modified.');
            $forcedsection = $cminfo->get_section_info($this->config->module_section);
            if (!$forcedsection) {
                lib::log_info('Section number not found.');
            } else {
                moveto_module($cm, $forcedsection);
                lib::log_info('Section number updated.');
            }
        }
    }
    /**
     * Implements get_topics for course_target.
     */
    static public function get_topics($user):array {
        // Collect all courses in which the user is teacher: Has any of:
        // 'mod/attendance:addinstance'
        $topics = [];

        $courses = get_user_capability_course('mod/attendance:addinstance', $user->id);
        if (empty($courses)) {
            return $topics;
        }
        foreach($courses as $course) {
            global $DB;
            // Get course data.
            $course = $DB->get_record('course', array('id' => $course->id), '*', MUST_EXIST);
            $topics[] = [
                'topicId' => 'course-' . $course->id,
                'name' => $course->shortname,
                'info' => $course->fullname,
                'externalIntegration' => true,
                'tag' => 'course',
            ];
        }
        return $topics;
    }
}
