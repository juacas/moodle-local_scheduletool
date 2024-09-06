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
use \Exception;
use \mod_attendance_structure;

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
    /**
     * Populate the class with the course and prefix.
     * @param string $topicId
     * @return void
     */
    function setup_from_topic_id(string $topicId)
    {
        global $DB;
        list($type, $prefix, $courseid) = self::parse_topic_id($topicId);
        $this->prefix = $prefix;
        $course = $DB->get_record('course', ['id' => $courseid], '*');
        if (!$course) {
            throw new \Exception("Invalid Topic {$topicId}:Course not found: {$courseid}");
        } else {
            $this->course = $course;
        }
    }
    /**
     * Parse topicId to get cmid and courseid.
     * @param string $topicId
     * @return array [type, prefix, courseid]
     */
    public static function parse_topic_id(string $topicId): array
    {
        $topicparts = explode('-', $topicId);
        if (count($topicparts) != 3 || $topicparts[0] == '' || $topicparts[1] != 'course' || !is_numeric($topicparts[2])) {
            throw new \Exception("Invalid course topicId format: {$topicId}");
        }
        return ['course', $topicparts[0], $topicparts[2]];
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
        $message = count($sessions) . ' attendance session(s) ' . json_encode($sessions) . ' found.';
        if (count($sessions) > 1) {
            $this->errors[] = $message;
            lib::log_error($message);
            return false;
        } else {
            lib::log_info($message);
            if (count($sessions) == 1) {
                $session = reset($sessions);
                $this->sessionid = $session->id;
                lib::log_info('Attendance session {$session->id} selected for update.');
            } else {
                $session = new \stdClass();
                $session->sessdate = $this->event->get_opening_time();
                $session->duration = $this->event->get_closing_time() == null ? 0 : $this->event->get_closing_time() - $this->event->get_opening_time();
                $session->groupid = 0;
                $session->description = $this->event->get_event_note();
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
                $this->sessionid = $sessid;

                lib::log_info('Attendance session created: ' . json_encode($session));
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
            $moduleinfo->introeditor = ['itemid' => -1, 'idnumber' => 0, 'text' => '', 'format' => FORMAT_PLAIN];
            $moduleinfo->cmidnumber = lib::CM_IDNUMBER;
            $moduleinfo->name = $this->config->module_name;
            $cmodule = create_module($moduleinfo);
            // Get cm_info.
            $cminfo = get_fast_modinfo($this->course);
            $cm = $cminfo->get_instances_of('attendance')[$cmodule->id];
            lib::log_info("New attendance activity created in course {$this->course->shortname} with: " . json_encode($cmodule));
            // Notify the module creation.
            lib::notify(
                $this->config,
                $this->event,
                $this->course->id,
                \core\output\notification::NOTIFY_INFO,
                get_string(
                    "notifications_new_activity",
                    "local_attendancewebhook",
                    [
                        'activityname' => $this->config->module_name,
                        'coursename' => $this->course->shortname,
                        'courseid' => $this->course->id,
                        'cmid' => $cm->id,
                        'activityurl' => new \moodle_url('/mod/attendance/view.php', ['id' => $cm->id]),
                        'courseurl' => new \moodle_url('/course/view.php', ['id' => $this->course->id])
                    ]
                )
            );

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
        if ($this->config->module_section != $cm->sectionnum) {
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
    static public function get_topics($user): array
    {
        // Collect all courses in which the user is teacher: Has any of:
        // 'mod/attendance:addinstance'
        $topics = [];

        $courses = get_user_capability_course('mod/attendance:addinstance', $user->id);
        if (empty($courses)) {
            return $topics;
        }
        $prefix = get_config('local_attendancewebhook', 'restservices_prefix');

        foreach ($courses as $course) {
            // Get course data.
            $course = lib::get_course_if_allowed($course->id);
            if ($course) {
                // Check start and end dates.
                $now = time();
                if (
                    ($course->startdate && ((int) $course->startdate) > $now) ||
                    ($course->enddate && ((int) $course->enddate) < $now)
                ) {
                    continue;
                }
                $calendar = self::get_course_calendar($course);
                $topics[] = (object) [
                    'topicId' => $prefix . '-course-' . $course->id,
                    'name' => $course->shortname,
                    'info' => $course->fullname,
                    'externalIntegration' => true,
                    'tag' => 'course',
                    'calendar' => $calendar,
                ];
            }
        }
        return $topics;
    }
    /**
     * Get course calendar.
     * @param object $course
     * @return object calendar structure.
     */
    static public function get_course_calendar($course): object
    {
        $cache_ttl = get_config('local_attendancewebhook', 'local_caches_ttl');
        // Use cache.
        $cache = \cache::make('local_attendancewebhook', 'course_calendar');
        $cachekey = 'course_calendar_' . $course->id;
        $calendar_cache = $cache_ttl > 0 ? $cache->get($cachekey) : false;

        if ($cache_ttl > 0 && $calendar_cache && $calendar_cache->time > (time() - $cache_ttl)) {
            return $calendar_cache->data;
        } else {
            // TODO: Get actual schedules.
            $calendar = (object) [
                'startDate' => $course->startdate ? date('Y-m-d', $course->startdate) : date('Y-m-d'),// format: 2021-09-01
                'endDate' => $course->enddate ? date('Y-m-d', $course->enddate) : null, // format: 2021-09-01
                'timetables' => [
                    [
                        'weekday' => "LMXJV",
                        'startTime' => "08:00",
                        'endTime' => "21:00",
                        "info" => $course->fullname,
                    ]
                ]
            ];
            // Store in cache.
            if ($cache_ttl > 0) {
                $cache->set($cachekey, (object)["data" => $calendar, "time" => time()]);
            }
        }
        return $calendar;
    }
}
