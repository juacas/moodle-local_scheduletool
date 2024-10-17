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

use moodle_database;

defined('MOODLE_INTERNAL') || die();
/**
 * Class to implement entities that can be targets of attendance marking.
 */
abstract class target_base
{
    public $cmid;
    public $cm;
    public $course;
    public $context;
    public $event;
    public $sessionid;
    public $server_time;
    public $errors = [];
    public $config;
    public $logtaker_user;
    public $prefix;

    public function __construct(object $event, $config)
    {
        $this->config = $config;
        $this->event = $event;
        $topicId = $event->getTopic()->get_topic_id();

        $this->setup_from_topic_id($topicId);
        // Find taker.
        $member = $event->getTopic()->get_member();
        $this->logtaker_user = lib::get_user($config, $member);
    }
    /**
     * TopicId is "modname-cmid-sessionid" or "course-courseid".
     * parse topicId to get cmid and sessionid.
     * @param string $topicId
     * @global \moodle_database $DB
     * @return void
     */
    protected function setup_from_topic_id(string $topicId)
    {
        global $DB;
        try {
            list($type, $prefix, $cmid, $sessionid) = self::parse_topic_id($topicId);
        } catch (\Exception $e) {
            $msg = 'Invalid topicId format: ' . $topicId;
            lib::log_error($msg);
            $this->errors[] = $msg;
            throw new \Exception($msg);
        }
        
        $this->type = $type;
        $this->prefix = $prefix;
        $this->sessionid = $sessionid;
        $this->set_cm($cmid);
        $this->course = $DB->get_record('course', array('id' => $this->cm->course), '*', MUST_EXIST);
    }
    /**
     * Parse topicId to get type, prefix, cmid and sessionid.
     * @param string $topicId
     * @throws \Exception
     * @return string[] array of type, prefix, and the rest of parts as: cmid and sessionid.
     */
    public static function parse_topic_id(string $topicId)
    {
        $topicparts = explode('-', $topicId);
        
        if (count($topicparts) < 3 || $topicparts[0] == '' || $topicparts[1] == '') {
            throw new \Exception("Invalid topicId format: {$topicId}");
        }
        $type = $topicparts[1];
        $prefix = $topicparts[0];
        $cmid = $topicparts[2] ?? null;
        $sessionid = $topicparts[3] ?? null;
        return [$type, $prefix, $cmid, $sessionid];
    }
    /**
     * Set de coursemodule. Load the coursemodule and the context.
     * @param mixed|\cm_info|\stdClass $cm id or cm object
     * @return void
     */
    public function set_cm($cm)
    {
        global $DB;
        if ($cm instanceof \cm_info) {
            $cm = $cm->get_course_module_record();
        }
        if ($cm instanceof \stdClass) {
            $this->cm = $cm;
            $this->cmid = $cm->id;
        } else { // $cm is an id.
            $this->cmid = $cm;
            $this->cm = $DB->get_record('course_modules', array('id' => $cm), '*', MUST_EXIST);
        }
        $this->context = \context_module::instance($this->cm->id);
    }
    public function get_prefix()
    {
        return $this->prefix;
    }
    public function getCourse()
    {
        return $this->course;
    }
    /**
     * Factory Method: Instantiate the target object depending on the event data.
     *  If topicId starts with 'course', it is a course.
     *  It topicId starts with 'hybridteaching', it is a hybrid teaching session.
     *  It topicId starts with 'attendance', it is an attendance session.
     * @param mixed $object
     * @param \local_attendancewebhook\event $event
     * @return object
     */
    static public function get_target(object $event, $config): target_base
    {
        list($type) = self::parse_topic_id($event->getTopic()->get_topic_id());
        

        // Check if the integration is enabled.
        if ($config->modhybridteaching_enabled == false && $type == 'hybridteaching') {
            throw new \Exception('mod_hybridteaching integration is disabled.');
        }
        if ($config->modattendance_enabled == false && ($type == 'attendance' || $type == 'course')) {
            throw new \Exception('mod_attendance integration is disabled.');
        }
        if ($config->export_courses_as_topics == false && $type == 'course') {
            throw new \Exception('Courses as topics is disabled.');
        }
        // Instantiate the target object depending on type.
        if ($type == 'course') {
            $classname = __NAMESPACE__ . '\\course_target';
        } else {
            $classname = __NAMESPACE__ . '\\mod' . $type . '_target';
        }
        return new $classname($event, $config);
    }
    /**
     * Get the session object.
     * @return mixed
     */
    public abstract function get_session();
    /**
     * Check and adjust the configuration of the target.
     */
    public abstract function check_configuration();
    /**
     * Register attendances from event data.
     */
    public function register_attendances()
    {
        // First check configuration of the target.
        $this->check_configuration();
        $attendances = $this->event->get_attendances();        
        foreach ($attendances as $attendance) {
            $this->register_attendance($attendance);
        }
    }
    /**
     * Register a single attendance from event data.
     * @param \local_attendancewebhook\attendance $attendance
     */
    public abstract function register_attendance(attendance $attendance);
    /**
     * Static function to get a list of topics for a user.
     * @param \stdClass $user user object.
     * @return topic[] array of topic objects.
     */
     public abstract static function get_topics(\stdClass $user): array;
    
}
