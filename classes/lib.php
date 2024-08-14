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

require_once ($CFG->dirroot . '/course/lib.php');
require_once ($CFG->dirroot . '/user/lib.php');

class lib
{

    const CM_IDNUMBER = 'local_attendancewebhook';

    const STATUS_DESCRIPTIONS = array(
        'UNKNOWN' => 'ASISTENCIA',
        'ON_SITE' => 'PRESENCIAL',
        'DISTANCE' => 'A DISTANCIA'
    );

    const STATUS_ACRONYMS = array(
        'UNKNOWN' => 'AS',
        'ON_SITE' => 'PR',
        'DISTANCE' => 'DS'
    );

    public static function get_event()
    {
        $json = file_get_contents("php://input");
        self::log_info('Request received: ' . $json);
        $object = json_decode($json);
        $event = new event($object);
        $message = 'Activity of type ' . $event->get_topic()->get_type() . '.';
        if ($event->get_topic()->get_type() !== 'COMMON') { // TODO: type COMMON???
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            return $event;
        }
    }
    public static function get_attendance_event()
    {
        $json = file_get_contents("php://input");
        self::log_info('Request received: ' . $json);
        $object = json_decode($json);
        $event = new attendance_event($object);
        $message = 'Activity of type ' . $event->get_topic()->get_type() . '.';
        if ($event->get_topic()->get_type() !== 'COMMON') { // TODO: type COMMON???
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            return $event;
        }
    }

    public static function get_config()
    {
        $config = get_config('local_attendancewebhook');
        if (
            empty($config->module_name) || !isset($config->module_section)
            || empty($config->course_id) || empty($config->member_id)
            || empty($config->user_id) || !isset($config->tempusers_enabled)
            || !isset($config->notifications_enabled)
        ) {
            self::log_error('Plugin misconfigured: ' . json_encode($config));
            return false;
        } else {
            self::log_info('Plugin configured: ' . json_encode($config));
            return $config;
        }
    }

    public static function get_module()
    {
        global $DB;
        $params = array('name' => 'attendance');
        $module = $DB->get_record('modules', $params);
        $message = 'Module ' . json_encode($params) . (!$module ? ' not' : '') . ' found.';
        !$module ? self::log_error($message) : self::log_info($message);
        return $module;
    }

    public static function get_course($config, $event)
    {
        global $DB;
        $params = array($config->course_id => $event->get_topic()->get_topic_id());
        $courses = $DB->get_records('course', $params);
        $message = count($courses) . ' course(s) ' . json_encode($params) . ' found.';
        if (count($courses) != 1) {
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            return $courses[array_keys($courses)[0]];
        }
    }
    /**
     * @param $config
     * @param $course
     * @param $module
     * @return bool|\stdClass
     */
    public static function get_course_module($config, $course, $module)
    {
        global $DB; // TODO: Use cm_info instead of course_modules.

        // Search attendance module in course with idnumber = self::CM_IDNUMBER using cached cm_modinfo.
        $cminfo = get_fast_modinfo($course);
        $cms = $cminfo->get_instances_of('attendance');
        $cms = array_filter($cms, function ($cm) {
            return $cm->idnumber === self::CM_IDNUMBER && $cm->deletioninprogress === 0;
        });
        $cm = reset($cms);

        $message = count($cms) . ' course modules(s) ' . json_encode($params) . ' found.';
        if (count($cms) > 1) {
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            if (count($cms) == 1) {
                $cm = $cms[array_keys($cms)[0]];
                $params = array('id' => $cm->instance);
                $attendance = $DB->get_record('attendance', $params);

                // Force the name. Why?
                if ($attendance->name != $config->module_name) { // TODO: Why name is restored?
                    self::log_info('Module name modified.');
                    set_coursemodule_name($cm->id, $config->module_name);
                    self::log_info('Module name updated.');
                }
                // Force to be in section $config->module_section.
                $params = array('course' => $course->id, 'section' => $config->module_section);
                $section = $DB->get_record('course_sections', $params);
                if (!$section || $section->id != $cm->section) {
                    self::log_info('Section number modified.');
                    if (!$section) {
                        self::log_info('Section number not found.');
                    } else {
                        moveto_module($cm, $section);
                        self::log_info('Section number updated.');
                    }
                }
                return $cm;
            } else {
                $moduleinfo = new \stdClass();
                $moduleinfo->course = $course->id;
                $moduleinfo->modulename = $module->name;
                $moduleinfo->section = $config->module_section;
                $moduleinfo->visible = 1;
                $moduleinfo->introeditor = array('text' => '', 'format' => FORMAT_PLAIN);
                $moduleinfo->cmidnumber = self::CM_IDNUMBER;
                $moduleinfo->name = $config->module_name;
                $cm = create_module($moduleinfo);
                self::log_info('Course module created.');
                // $cm = $DB->get_record('course_modules', $params); // This gets also cms in deletion progress state.
                $DB->delete_records('attendance_statuses', array('attendanceid' => $cm->instance)); // TODO: This is not necessary.
                foreach (self::STATUS_DESCRIPTIONS as $name => $description) {
                    $status = new \stdClass();
                    $status->attendanceid = $cm->instance;
                    $status->acronym = self::STATUS_ACRONYMS[$name];
                    $status->description = $description;
                    $status->id = $DB->insert_record('attendance_statuses', $status);
                    self::log_info('Attendance status "' . $status->description . '" created.');
                }
                return $cm;
            }
        }
    }

    public static function get_user_enrol($config, $member, $course)
    {
        global $DB;
        // TODO: Upe Moodle API.
        $user = lib::get_user($config, $member);
        $context = \context_course::instance($course->id);
        // Check if user is enrolled in course by userid (is cached).
        if (!$user || !is_enrolled($context, $user->id)) {
            return false;
        } else {
            return $user;
        }
    }
    /**
     * Returns the value of the field configured to be used as member id.
     * @see settings.php
     */
    private static function get_member_id($config, member $member)
    {
        if ($config->member_id === 'username') {
            return $member->get_username();
        } else if ($config->member_id === 'email') {
            return $member->get_email();
        } else {
            return '';
        }
    }

    public static function is_tempusers_enabled($config)
    {
        if (!$config->tempusers_enabled) {
            self::log_info('Temporary users disabled.');
            return false;
        } else {
            self::log_info('Temporary users enabled.');
            return true;
        }
    }

    /**
     * Gets or creates a temporary user for the given attendance.
     */
    public static function get_tempuser($attendance, $course)
    {
        global $DB;
        $params = array('email' => $attendance->get_member()->get_email(), 'courseid' => $course->id);
        $tempusers = $DB->get_records('attendance_tempusers', $params);
        $message = count($tempusers) . ' attendance temporary user(s) ' . json_encode($params) . ' found.';
        if (count($tempusers) > 1) {
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            if (count($tempusers) == 1) {
                return $tempusers[array_keys($tempusers)[0]];
            } else {
                $user = new \stdClass();
                $user->confirmed = 1;
                $user->idnumber = self::CM_IDNUMBER;
                do {
                    $user->username = uniqid() . '@' . self::CM_IDNUMBER;
                    $params = array('username' => $user->username);
                    $found = $DB->get_record('user', $params);
                } while ($found);
                $user->email = $user->username;
                $user->id = user_create_user($user, false, false);
                $user->deleted = 1;
                user_update_user($user, false, false);
                self::log_info('User ' . $user->username . ' created.');
                $tempuser = new \stdClass();
                $tempuser->studentid = $user->id;
                $tempuser->courseid = $course->id;
                $tempuser->fullname = $attendance->get_member()->get_firstname() . ' ' . $attendance->get_member()->get_lastname();
                $tempuser->email = $attendance->get_member()->get_email();
                $tempuser->created = time();
                $tempuser->id = $DB->insert_record('attendance_tempusers', $tempuser);
                self::log_info('Attendance temporary user created.');
                return $tempuser;
            }
        }
    }

    public static function log_info($message)
    {
        self::log($message, 'INFO');
    }

    public static function log_error($message)
    {
        self::log($message, 'ERROR');
    }
    // TODO: Use moodle logger.
    private static function log($message, $type)
    {
        global $CFG;
        $dir = $CFG->dataroot . DIRECTORY_SEPARATOR . self::CM_IDNUMBER . DIRECTORY_SEPARATOR . 'logs';
        if (!file_exists($dir) && !mkdir($dir, 0777, true)) {
            return;
        }
        $file = $dir . DIRECTORY_SEPARATOR . 'trace.log';
        $maxcount = 10;
        $maxsize = 5000000; // 5MB en bytes.
        if (file_exists($file) && filesize($file) >= $maxsize) {
            $oldest = $file . "." . $maxcount;
            if (file_exists($oldest)) {
                unlink($oldest);
            }
            for ($i = $maxcount; $i > 0; $i--) {
                $current = $file . "." . $i;
                if (file_exists($current)) {
                    $next = $file . "." . ($i + 1);
                    rename($current, $next);
                }
            }
            rename($file, $file . ".1");
        }
        file_put_contents($file, date('Y-m-d H:i:s') . ' ' . $type . ' ' . $message . "\n", FILE_APPEND);
    }

    public static function notify_error($config, $event, $attendances = null)
    {
        if (!$config->notifications_enabled) {
            self::log_info('Notifications disabled.');
            return;
        }
        self::log_info('Notifications enabled.');
        $user = self::get_user($config, $event->get_topic()->get_member());
        if ($user) {
            $message = new \core\message\message();
            $message->component = 'local_attendancewebhook';
            $message->name = 'error';
            $message->userfrom = \core_user::get_noreply_user();
            $message->userto = $user->id;
            $message->subject = 'Moodle ' . $config->module_name . ': ' . get_string('notification_subject', 'local_attendancewebhook');
            if ($attendances) {
                $text = get_string('notification_error_attendances', 'local_attendancewebhook');
            } else {
                $text = get_string('notification_error_event', 'local_attendancewebhook');
            }
            $message->fullmessage = $text . ' ' . strval($event) . '.';
            $message->fullmessagehtml = '<p>' . $text . '</p><p>' . strval($event) . '</p>';
            if ($attendances) {
                $message->fullmessage .= ' ' . get_string('notification_attendances', 'local_attendancewebhook');
                foreach ($attendances as &$attendance) {
                    $message->fullmessage .= ' ' . strval($attendance) . ',';
                    $message->fullmessagehtml .= '<p>' . strval($attendance) . '</p>';
                }
                $message->fullmessage = substr($message->fullmessage, 0, strlen($message->fullmessage) - 1) . '.';
            }
            $text = get_string('notification_contact_admin', 'local_attendancewebhook');
            $message->fullmessage .= ' ' . $text;
            $message->fullmessagehtml .= '<p>' . $text . '</p>';
            $message->fullmessageformat = FORMAT_HTML;
            $message->smallmessage = $message->fullmessage;
            $message->notification = 1;
            message_send($message);
            self::log_info('Notification sent: ' . $message->fullmessagehtml);
        }
    }

    public static function get_user($config, member $member)
    {
        global $DB;
        $params = array($config->user_id => self::get_member_id($config, $member));
        $users = $DB->get_records('user', $params);
        $message = count($users) . ' users(s) ' . json_encode($params) . ' found.';
        if (count($users) != 1) {
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            return reset($users);
        }
    }
    /**
     * Get local topics for the given user.
     */
    public static function get_local_topics($user)
    {
        $topics = [];
        // Courses can't be topics without POD.
        // TODO: Get Timetables from POD.
        if (get_config('local_attendancewebhook', 'export_courses_as_topics')) {
            $courses = course_target::get_topics($user);
            $topics = array_merge($topics, $courses);
        }
        if (get_config('local_attendancewebhook', 'modattendance_enabled')) {
            $mods = modattendance_target::get_topics($user);
            $topics = array_merge($topics, $mods);
        }
        if (get_config('local_attendancewebhook', 'modhybridteaching_enabled')) {
            $mods = modhybridteaching_target::get_topics($user);
            $topics = array_merge($topics, $mods);
        }
        return $topics;
    }
    /**
     * Get remote topics for the given user.
     */
    public static function get_remote_topics($user)
    {
        $topics = [];
        $remotes = lib::get_remotes();
       
        foreach ($remotes as $prefix => $url) {
          
            try {
                $request_url = $url . '&userid=' . $user->id;
                // Make a request to the endpoint with curl.
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $request_url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($curl);
                if ($response === false) {
                    lib::log_error('Error getting topics from ' . $request_url . ': ' . curl_error($curl));
                    continue;
                }
                // Parse the response.
                $response = json_decode($response);
                if (empty($response->topics)) {
                    continue;
                }
                $topics = array_merge($topics, $response->topics);
            } catch (\Exception $e) {
                lib::log_error('Error getting topics from ' . $request_url . ': ' . $e->getMessage());
            } catch (\Throwable $e) {
                lib::log_error('Error getting topics from ' . $request_url . ': ' . $e->getMessage());
            }
        }
        return $topics;
    }
    /**
     * Get user_data from the given user.
     * @param $userid string User id.
     * @return \stdClass|false user_data structure or false if user not found.
     */
    public static function get_user_data($userid)
    {
        global $DB;
        $useridfield = get_config('local_attendancewebhook', 'user_id');
        $user = $DB->get_record('user', [$useridfield => $userid], '*');
        if (!$user) {
            return false;
        }
        /*
        Compose userdata response in JSON format.
        {
         "userName": "userName",
         "firstName": "firstName",
         "secondName": "secondName",
         "dni": "dni",
         "nia": "nia",
         "email": "email",
         "rol": "ORGANISER
        }
        */

        $NIAField = get_config('local_attendancewebhook', 'field_NIA');
        // Get NIA from user profile.
        if (empty($user->$NIAField)) {
            // If NIA is empty, look into custom fields.
            $customfields = profile_get_custom_fields($user->id);
            foreach ($customfields as $field) {
                if ($field->shortname == $NIAField) {
                    $nia = $field->data;
                    break;
                }
            }
        } else {
            $nia = $user->$NIAField;
        }

        // Roles are:
// ORGANISER: users with capability to mod/attendance:takeattendances in any course.
// ATTENDEE: users without it.
// TODO: Check mod/hybridteaching:createsessions

        $attendancecourses = get_user_capability_course('mod/attendance:takeattendances', $user->id);

        if ($attendancecourses && count($attendancecourses) > 0) {
            $user->rol = 'ORGANISER';
        } else {
            $user->rol = 'ATTENDEE';
        }

        $userresponse = [
            'userName' => $user->username,
            'firstName' => $user->firstname,
            'secondName' => $user->lastname,
            'dni' => $user->idnumber,
            'nia' => $nia,
            'email' => $user->email,
            'rol' => $user->rol,
        ];
        return $userresponse;
    }
    /**
     * Get user_data_remote from the given user.
     * @param $userid string User id.
     * @return array User_data structures collected. Empty array if no user found.
     */
    public static function get_user_data_remote($userid): array {
        $remotes = lib::get_remotes();
        $userresponse = [];
        foreach ($remotes as $prefix => $url) {
            try {
                $request_url = $url . '&userid=' . $userid;
                // Make a request to the endpoint with curl.
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $request_url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($curl);
                if ($response === false) {
                    lib::log_error('Error getting user data from ' . $request_url . ': ' . curl_error($curl));
                    continue;
                }
                // Parse the response.
                $response = json_decode($response);
                if (empty($response->user)) {
                    continue;
                }
                $userresponse = array_merge($userresponse, $response->user);
            } catch (\Exception $e) {
                lib::log_error('Error getting user data from ' . $request_url . ': ' . $e->getMessage());
            } catch (\Throwable $e) {
                lib::log_error('Error getting user data from ' . $request_url . ': ' . $e->getMessage());
            }
        }
        return $userresponse;
    }
    /**
     * Get configured remotes.
     */
    public static function get_remotes() {
        $remotes = get_config('local_attendancewebhook', 'restservices_getTopics');
        // Each line is a proxyed endpoint with fomat: Prefix|URL.
        // Split lines.
        $remotes = explode("\n", $remotes);
        // Remove empty lines.
        $remotes = array_filter($remotes);
        // Create key-value array.
        $remotes_result = [];
        foreach ($remotes as $remote) {
            $parts = explode('|', $remote);
            $remotes_result[$parts[0]] = $parts[1];
        }
        return $remotes_result;
    }
}
