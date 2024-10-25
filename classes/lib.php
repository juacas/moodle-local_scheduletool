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
use PhpXmlRpc\Exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/user/lib.php');

class lib
{
    const WEEK_DAYS = ["L", "M", "X", "J", "V", "S", "D"];
    const WEEK_DAYS_I18N = [ 'monday' => 'L', 'tuesday' => 'M', 'wednesday' => 'X', 'thursday' => 'J', 'friday' => 'V', 'saturday' => 'S', 'sunday' => 'D'];
    const CM_IDNUMBER = 'local_scheduletool';
    // TODO: i18n status descriptions.
    const STATUS_DESCRIPTIONS = array(
        'NOTPRESENT' => 'NO PRESENTADO',
        'UNKNOWN' => 'ASISTENCIA',
        'ON_SITE' => 'PRESENCIAL',
        'DISTANCE' => 'A DISTANCIA'
    );

    const STATUS_ACRONYMS = array(
        'NOTPRESENT' => 'NP',
        'UNKNOWN' => 'AS',
        'ON_SITE' => 'PR',
        'DISTANCE' => 'DS'
    );

    public static function get_event()
    {
        $json = file_get_contents("php://input");
        self::log_info('Request received: ' . $json);
        $event = new event($json);
        $message = 'Activity of type ' . $event->getTopic()->get_type() . '.';
        if ($event->getTopic()->get_type() !== 'COMMON') {
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

        $event = new attendance_event($json);
        $message = 'Activity of type ' . $event->getTopic()->get_type() . '.';
        if ($event->getTopic()->get_type() !== 'COMMON') { // TODO: type COMMON???
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            return $event;
        }
    }

    public static function get_config()
    {
        $config = get_config('local_scheduletool');
        // if (
        //     empty($config->module_name) || !isset($config->module_section)
        //     || empty($config->course_id) || empty($config->member_id)
        //     || empty($config->user_id) || !isset($config->tempusers_enabled)
        //     || !isset($config->notifications_enabled)
        // ) {
        //     self::log_error('Plugin misconfigured: ' . json_encode($config));
        //     return false;
        // } else {
        //     self::log_info('Plugin configured: ' . json_encode($config));
        //     return $config;
        // }
        return $config;
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
        $params = array($config->course_id => $event->getTopic()->get_topic_id());
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
     * Check if $member is enrolled y the course and return the user object.
     * @param mixed $config
     * @param mixed $member
     * @param mixed $course
     * @return mixed
     */
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
        if (get_config('local_scheduletool', 'logs_enabled') == false) {
            return;
        }
        $dir = $CFG->dataroot . DIRECTORY_SEPARATOR . self::CM_IDNUMBER . DIRECTORY_SEPARATOR . 'logs';
        if (!file_exists($dir) && !mkdir($dir, 0777, true)) {
            return;
        }
        $file = $dir . DIRECTORY_SEPARATOR . 'trace.log';
        $maxcount = 10;
        $maxsize = 500000; // 5MB en bytes.
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
        // If message is array or object, convert to string.
        if (is_array($message) || is_object($message)) {
            $message = json_encode($message);
        }
        file_put_contents($file, date('Y-m-d H:i:s') . ' ' . $type . ' ' . $message . "\n", FILE_APPEND);
    }
    /**
     * 
     * Sends a notification to the user (logtaker).
     * @param mixed $config
     * @param mixed $event
     * @param mixed $level \core\output\notification::NOTIFY_INFO|\core\output\notification::NOTIFY_ERROR
     * @param mixed $attendances
     * @return void
     */
    public static function notify($config, $event, $courseid, $level, $attendances = null)
    {
        if (!$config->notifications_enabled) {
            self::log_info('Notifications disabled.');
            return;
        }
        if (!is_array($attendances)) {
            $attendances = [$attendances];
        }
        self::log_info('Notifications enabled.');
        $user = self::get_user($config, $event->get_logtaker());
        if ($user) {
            $message = new \core\message\message();
            $message->component = 'local_scheduletool';
            $message->userfrom = \core_user::get_noreply_user();
            $message->courseid = $courseid;
            $message->userto = $user; // TODO: $user->lang should set the language.
            $message->name = 'notification'; // Provider local_scheduletool/notification. It's the only one declared.

            $eventstr = strval($event);
            $a = (object) [
                'event' => $event,
                'module' => $config->module_name,
                'topic' => $event->getTopic(),
                'opening_time' => date('d-m-Y H:i', $event->get_opening_time()),
                'closing_time' => date('d-m-Y H:i', $event->get_closing_time()),
                'eventstr' => $eventstr,
            ];

            $message->subject = get_string('notification_subject', 'local_scheduletool', $a);
            if ($level == \core\output\notification::NOTIFY_ERROR) {
                if ($attendances) {
                    $text = get_string('notification_error_attendances', 'local_scheduletool', $a);
                } else {
                    $text = get_string('notification_error_event', 'local_scheduletool', $a);
                }
                $admintext = get_string('notification_contact_admin', 'local_scheduletool', $a);
            } else {
                $text = get_string('notification_info', 'local_scheduletool', $a);
                $admintext = '';
            }

            $message->fullmessage = "{$text}\n";
            $message->fullmessagehtml = "<p>{$text}</p>";
            if ($attendances) {
                $message->fullmessage .= get_string('notification_messages', 'local_scheduletool') . "\n";
                foreach ($attendances as &$attendance) {
                    $attendancestr = strval($attendance);
                    $message->fullmessage .= "{$attendancestr} \n";
                    $message->fullmessagehtml .= "<p> {$attendancestr} </p>";
                }
                //$message->fullmessage = substr($message->fullmessage, 0, strlen($message->fullmessage) - 1);
            }

            $message->fullmessage .= "{$admintext}\n";
            $message->fullmessagehtml .= "<p>$admintext </p>";
            $message->fullmessageformat = FORMAT_HTML;
            $message->smallmessage = $message->fullmessage;
            $message->notification = 1;
            message_send($message);
            self::log_info('Notification sent: ' . $message->fullmessagehtml);
        }
    }
    /**
     * Find the user with the given member data.
     * @param mixed $config
     * @param \local_scheduletool\member $member
     * @return mixed
     */
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
     * Autoenrol member if course has a self-enrolment method named 'AttendanceEnrollment'.
     * 
     */
    public static function autoenrol_user($config, $member, $course) {
        global $DB;
        $enrolinstances = enrol_get_instances($course->id, true);
        $enrolinstance = array_filter($enrolinstances, function ($instance) {
            return $instance->enrol === 'self' && $instance->name === 'AttendanceEnrollment';
        });
        if (count($enrolinstance) != 1) {
            self::log_error('Self-enrolment method not found in course: ' . $course->id);
            return false;
        }
        $instance = reset($enrolinstance);
        $plugin = enrol_get_plugin('self');
        // Find userid from member data.
        $user = self::get_user($config, $member);
        
        if (!$user) {
            self::log_error("User not found: " . json_encode($member));
            return false;
        }
        $plugin->enrol_user($instance, $user->id, $instance->roleid);
        return $user;
    }
    /**
     * Get local topics for the given user.
     * @param $user object User object.
     * @param $fromdate int From date timestamp.
     * @param $todate int To date timestamp.
     * @return array topics structure.
     */
    public static function get_local_topics($user, $fromdate = null, $todate = null, bool $compress = false)
    {
        $cache_ttl = get_config('local_scheduletool', 'local_caches_ttl');
        // Use cache.
        $cache = \cache::make('local_scheduletool', 'user_topics');
        $cachekey = "user_topic_{$user->id}_{$fromdate}_{$todate}";
        if ($cache_ttl > 0 && $cached_topic = $cache->get($cachekey)) {
            // Check lifetime.
            if ($cached_topic->time > (time() - $cache_ttl)) {
                return $cached_topic->data;
            }
        }
        // Cache miss or expired.
        $topics = [];
        if (get_config('local_scheduletool', 'export_courses_as_topics')) {
            $courses = course_target::get_topics($user, $fromdate, $todate, $compress);
            $topics = array_merge($topics, $courses);
        }
        if (get_config('local_scheduletool', 'modattendance_enabled')) {
            $mods = modattendance_target::get_topics($user);
            $topics = array_merge($topics, $mods);
        }
        if (get_config('local_scheduletool', 'modhybridteaching_enabled')) {
            $mods = modhybridteaching_target::get_topics($user);
            $topics = array_merge($topics, $mods);
        }
        // Store in cache.
        if ($cache_ttl > 0) {
            $cache->set($cachekey, (object) ['time' => time(), 'data' => $topics]);
        }
        return $topics;
    }
    /**
     * Get remote topics for the given user.
     */
    public static function get_remote_topics($userid)
    {
        // Use remote topics cache.
        $cache_ttl = get_config('local_scheduletool', 'remote_caches_ttl');
        $cache = \cache::make('local_scheduletool', 'user_topics');
        $cachekey = "remote_user_topic_{$userid}";
        if ($cache_ttl > 0 && $cached_topic = $cache->get($cachekey)) {
            // Check lifetime. 1800 seconds for remote topics.
            if ($cached_topic->time > (time() - $cache_ttl)) {
                return $cached_topic->data;
            }
        }
        $topics = [];
        $remotes = lib::get_remotes('restservices_getTopics');

        foreach ($remotes as $prefix => $url) {

            try {
                $request_url = $url . '&userid=' . $userid;
                // Make a request to the endpoint with curl.
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $request_url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                // Add cookie for X_DEBUG_SESSION.
                curl_setopt($curl, CURLOPT_COOKIE, "XDEBUG_SESSION=XDEBUG_ECLIPSE");
                $response = curl_exec($curl);
                $info = curl_getinfo($curl);
                if ($info['http_code'] == 401) {
                    lib::log_error('Unauthorized access to ' . $request_url);
                    continue;
                }
                if ($info['http_code'] == 404) {
                    continue;
                }
                if ($response === false) {
                    lib::log_error('Error getting topics from ' . $request_url . ': ' . curl_error($curl));
                    continue;
                }
                // Parse the response.
                $responsejson = json_decode($response);
                if (empty($responsejson->topics)) {
                    continue;
                }
                $topics = array_merge($topics, $responsejson->topics);
                // Store in cache.
                if ($cache_ttl > 0) {
                    $cache->set($cachekey, (object) ['time' => time(), 'data' => $topics]);
                }
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
        $useridfield = get_config('local_scheduletool', 'user_id');
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

        $NIAField = get_config('local_scheduletool', 'field_NIA');
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
// ORGANISER: users with capability to mod/attendance:takeattendances in any allowed course.
// ATTENDEE: users without it.
// TODO: Check mod/hybridteaching:createsessions
        $user->rol = 'ATTENDEE';

        $attendancecourses = get_user_capability_course('mod/attendance:takeattendances', $user->id);
        if ($attendancecourses) {
            $attendancecourses = array_filter($attendancecourses, function ($course) {
                return lib::get_course_if_allowed($course->id);
            });
            if (count($attendancecourses) > 0) {
                $user->rol = 'ORGANISER';
            }
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
        return (object) $userresponse;
    }
    /**
     * Get user_data_remote from the given user.
     * May return multiple user_data structures for the same user from the proxyed endpoints.
     * @param $userid string User id.
     * @return array User_data structures collected. Empty array if no user found.
     */
    public static function get_user_data_remote($userid): array
    {
        $remotes = lib::get_remotes('restservices_getUserData');
        $userresponse = [];
        foreach ($remotes as $prefix => $url) {
            try {
                $request_url = $url . '&userid=' . $userid;
                // Make a request to the endpoint with curl.
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $request_url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                // Add cookie for X_DEBUG_SESSION.
                curl_setopt($curl, CURLOPT_COOKIE, "XDEBUG_SESSION=XDEBUG_ECLIPSE");
                $response = curl_exec($curl);
                $info = curl_getinfo($curl);
                if ($info['http_code'] == 401) {
                    lib::log_error('Unauthorized access to ' . $request_url);
                    continue;
                }
                if ($response === false) {
                    lib::log_error('Error getting user data from ' . $request_url . ': ' . curl_error($curl));
                    continue;
                }
                // Parse the response.
                $responsejson = json_decode($response);
                if (empty($responsejson->userName)) {
                    lib::log_error('No user data found in response from ' . $request_url . " Rseponse: " . $response);
                    continue;

                }
                $userresponse[] = $responsejson;
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
    public static function get_remotes($type)
    {
        $remotes = get_config('local_scheduletool', $type);
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
    /**
     * Process save_attendance, SignUp request.
     */
    public static function process_save_attendance()
    {
        $remotes = \local_scheduletool\lib::get_remotes('restservices_signUp');
        $event = \local_scheduletool\lib::get_attendance_event();
        return self::process_register_attendance_with_proxys($event, $remotes);
    }
    /**
     * Process add_session, CloseEvent request.
     * @return bool|array True if success. Array of errors otherwise.
     */
    public static function process_add_session()
    {
        $remotes = \local_scheduletool\lib::get_remotes('restservices_closeEvent');
        $event = \local_scheduletool\lib::get_event();
        return self::process_register_attendance_with_proxys($event, $remotes);
    }
    /**
     * Process register_attendance request.
     * @param $event \local_scheduletool\attendance_event|\local_scheduletool\attendance_event
     * @param $remotes array of remotes for the service.
     * @see \local_scheduletool\lib::get_remotes
     * @return bool|array True if success. Array of errors otherwise.
     */
    public static function process_register_attendance_with_proxys($event, $remotes)
    {
        if (!$event) {
            return false;
        }

        $config = \local_scheduletool\lib::get_config();
        if (!$config) {
            return false;
        }
        $att_target = null;

        try {
            $errors = [];

            // If Rest services are enabled, check topicId format.
            if ($config->restservices_enabled) {
                global $DB, $CFG, $USER;

                list($type, $prefix) = \local_scheduletool\target_base::parse_topic_id($event->getTopic()->get_topic_id());
                // Get proxies.

                if ($prefix == $config->restservices_prefix) {
                    $att_target = \local_scheduletool\target_base::get_target($event, $config);
                    $att_target->errors = &$errors;
                    $logtaker = \local_scheduletool\lib::get_user($config, $event->get_logtaker());
                    if (!$logtaker) {
                        throw new \Exception('Unknown logtaker:' . $event->get_logtaker());
                    }
                    lib::log_info("Logtaker: {$logtaker->username}");
                    // Impersonates the logtaker user.
                    $USER = $logtaker;
                    force_current_language($logtaker->lang);
                    // Local request.
                    $att_target->register_attendances();
                } else if (isset($remotes[$prefix])) {
                    $request_url = $remotes[$prefix];
                    lib::log_info("Forwarding request to: $request_url");
                    // Make a request to the endpoint with curl.
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $request_url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    // Set the request as POST.
                    curl_setopt($curl, CURLOPT_POST, true);
                    // Set the request data as JSON.
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $event->get_source());
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    if ($CFG->debug >= DEBUG_DEVELOPER) {
                        // Debugging.
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                        // Add cookie for X_DEBUG_SESSION.
                        curl_setopt($curl, CURLOPT_COOKIE, "XDEBUG_SESSION=XDEBUG_ECLIPSE");
                    }

                    $response = curl_exec($curl);
                    $info = curl_getinfo($curl);
                    if ($info['http_code'] == 401) {
                        $errors[] = 'Unauthorized access to ' . $request_url;
                    }
                    if ($response === false) {
                        $errors[] = 'Error connection proxy ' . $request_url . ': ' . curl_error($curl);
                    }
                    if ($response != 'true') {
                        $errors[] = 'Error processing request to ' . $request_url . ': ' . $response;
                    }
                } else {
                    $errors[] = 'Invalid prefix: ' . $prefix;
                }
            }

        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        if (count($errors) > 0) {
            $courseid = $att_target ? $att_target->getCourse()->id ?? 0 : 0;
            lib::log_error($errors);
            \local_scheduletool\lib::notify($config, $event, $courseid, \core\output\notification::NOTIFY_ERROR, $errors);
            // One error means that the attendance was not saved.
            return $errors;
        } else {
            return true;
        }
    }


    //     global $CFG;
    //     try {
    //         if (!$event) {
    //             return false;
    //         }

    //         $config = \local_scheduletool\lib::get_config();
    //         if (!$config) {
    //             return false;
    //         }
    //         $errors = [];
    //         // If Rest services are enabled, load controller for topicId type.
    //         if ($config->restservices_enabled) {
    //             // Impersonates the logtaker user.
    //             global $USER;
    //             $logtaker = \local_scheduletool\lib::get_user($config, $event->get_logtaker());
    //             if (!$logtaker) {
    //                 throw new \Exception('Unknown logtaker:' . $event->get_logtaker());
    //             }
    //             $USER = $logtaker;


    //             list($type, $prefix, $topicId) = \local_scheduletool\target_base::parse_topic_id($event->getTopic()->get_topic_id());
    //             if ($prefix == $config->restservices_prefix) {
    //                 // Local request.
    //                 $att_target = \local_scheduletool\target_base::get_target($event, $config);
    //                 $att_target->errors = &$errors;
    //                 $att_target->register_attendances();

    //             } else if (array_key_exists($prefix, $remotes)) {
    //                     // Execute a proxyed request.
    //                     $request_url = $remotes[$prefix];
    //                     // Make a request to the endpoint with curl.
    //                     $curl = curl_init();
    //                     curl_setopt($curl, CURLOPT_URL, $request_url);
    //                     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //                     // Set the request as POST.
    //                     curl_setopt($curl, CURLOPT_POST, true);
    //                     // Set the request data as JSON.
    //                     curl_setopt($curl, CURLOPT_POSTFIELDS, $event->get_source());
    //                     curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    //                     if ($CFG->debug >= DEBUG_DEVELOPER) {
    //                         // Debugging.
    //                         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    //                         curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //                         // Add cookie for X_DEBUG_SESSION.
    //                         curl_setopt($curl, CURLOPT_COOKIE, "XDEBUG_SESSION=XDEBUG_ECLIPSE");
    //                     }

    //                     $response = curl_exec($curl);
    //                     $info = curl_getinfo($curl);
    //                     if ($info['http_code'] == 401) {
    //                         throw new \Exception('Unauthorized access to ' . $request_url);
    //                     }
    //                     return $response;                    

    //             } else {
    //                 $errors[] = 'Invalid prefix: ' . $prefix;
    //             }

    //         }

    //     } catch (\Exception $e) {
    //         $errors[] = $e->getMessage();
    //     }

    //     if (count($errors) > 0) {
    //         lib::log_error($errors);
    //         \local_scheduletool\lib::notify_error($config, $event, $errors);
    //         // One error means that the attendance was not saved.
    //         return $errors;
    //     } else {
    //         return true;
    //     }
    // }
    // Static cache.
    static $allowed_categories = null;
    /**
     * Get allowed course categories.
     * @return array|bool of allowed category ids. True if all categories are allowed.
     */
    public static function get_allowed_categories()
    {
        if (self::$allowed_categories === null) {
            $categories = get_config('local_scheduletool', 'enableincategories');
            self::$allowed_categories = lib::get_fulltree_categories($categories);
        }
        return self::$allowed_categories;
    }
    // Local cache.
    static $disallowed_categories = null;
    /**
     * Get disallowed course categories.
     * @return bool|array of disallowed category ids. True if all categories are allowed.
     */
    public static function get_disallowed_categories()
    {

        if (self::$disallowed_categories === null) {
            $categories = get_config('local_scheduletool', 'disableincategories');
            self::$disallowed_categories = lib::get_fulltree_categories($categories);
        }
        return self::$disallowed_categories;
    }
    /**
     * Get full tree of ids of categories from the input set of categories.
     * Include the input categories and all their children.
     * @param string $categories ids of categories separated by commas.
     * @return array of category ids.
     */
    public static function get_fulltree_categories(string $categories)
    {
        if (!empty($categories)) {
            $categories = explode(',', $categories);
            // $categories = array_map('intval', $categories);
            $subcats = [];
            foreach ($categories as $category) {
                $cat = \core_course_category::get($category, IGNORE_MISSING);
                if (!$cat) {
                    lib::log_error('Category not found: ' . $category);
                    continue;
                }
                $children = $cat->get_all_children_ids(); // Its cached so no problem.
                $subcats = array_merge($subcats, $children);
            }
            $categories = array_merge($categories, $subcats);
            return $categories;
        } else {
            return [];
        }
    }
    /**
     * Check if a course is allowed.
     * @param $courseid int Course id.
     * @return \stdClass|bool Course object if allowed. False otherwise.
     */
    public static function get_course_if_allowed($courseid)
    {
        global $DB;
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        if ($course === false) {
            return false;
        }
        // Check if this course is redirected.
        if (get_config('local_scheduletool', 'skip_redirected_courses') && $course->format == 'redirected') {
            return false;
        }
        // Get disallowed categories.
        $disalloedcategories = \local_scheduletool\lib::get_disallowed_categories();
        if (count($disalloedcategories) > 0 && in_array($course->category, $disalloedcategories)) {
            return false;
        }
        // Get allowed categories.
        $categories = \local_scheduletool\lib::get_allowed_categories();
        if (count($categories) > 0 && in_array($course->category, $categories)) {
            return $course;
        } else {
            return false;
        }
    }
    /**
     * Get redirected courses.
     * @param \stdClass $course
     * @return array 
     */
    public static function get_schedule_equivalent_courses($course): array
    {
        // Get role method type meta.
        global $DB;
        $redirected = [];
        $metalinked = $DB->get_records('enrol', array('courseid' => $course->id, 'enrol' => 'meta'), 'customint1', 'customint1');
        if (!empty($metalinked)) {
            // Search courses in the meta linked list that has "redirected" format.
            $redirected = $DB->get_records_list('course', 'id', array_keys($metalinked), 'id');
            $redirected = array_filter($redirected, function ($course) {
                return $course->format == 'redirected';
            });
        }
        $redirected[] = $course;
        return $redirected;
    }
    /**
     * Collect calendar events grouping by week calculating weekday.
     * return timetable structure:
     * {
     * startDate: 'Y-m-d',
     * endDate: 'Y-m-d',
     * timetables: [
     * {
     *  weekdays: 'L,M,X,J,V,S,D',
     *  startTime: 'HH:MM',
     *  endTime: 'HH:MM',
     *  info: 'info'
     * }
     * ]
     * @param array $data of slot events.
     * @param bool $collectbyweek if true, group by week.
     * @return array of calendar events grouped by week. key is week number. value is an object with timetable structure.
     */
    public static function collect_calendars($data, $collectbyweek = false): array
    {
        // Iterate grouping in a week.
        $weekdays = ["L", "M", "X", "J", "V", "S", "D"];
        $calendars_by_week = [];
        foreach ($data as $slot) {
            // Complete fechainicio and fechafin if only has fecha (in exams structure).
            // "fecha" format is d/m/Y convert to Y-m-d.
            $fecha = date('Y-m-d', strtotime(str_replace('/', '-', $slot->fecha)));
            if (!isset($slot->fechaInicio)) {
                $slot->fechaInicio = $fecha;
            }
            if (!isset($slot->fechaFin)) {
                $slot->fechaFin = $fecha;
            }
            $weeknumber = date('W', strtotime($slot->fechaInicio));
            $weekday = date('N', strtotime($slot->fechaInicio));
            $timetable = (object) [
                'weekdays' => $weekdays[$weekday - 1],
                'startTime' => $slot->horaInicio,
                'endTime' => $slot->horaFin,
                "info" => "$slot->nombreGrupo ($slot->nombreUbicacion)",
            ];

            if ($collectbyweek && $calentry = $calendars_by_week[$weeknumber] ?? false) {
                // Add timetable to existent calentry if not exists.
                if (!in_array($timetable, $calentry->timetables)) {
                    $calentry->timetables[] = $timetable;
                }
            } else {
                // Get "monday this week" for $slot->fechaInicio.
                $startweek = strtotime('monday this week', strtotime($slot->fechaInicio));
                $startweekdate = date('Y-m-d', $startweek);
                $endweekdate = date('Y-m-d', strtotime($startweekdate . ' + 6 days'));
                // Create new entry.
                $calentry = (object) [
                    'startDate' => $startweekdate,
                    'endDate' => $endweekdate,
                    'timetables' => [$timetable],
                ];

                if ($collectbyweek) {
                    $calendars_by_week[$weeknumber] = $calentry;
                } else {
                    $calendars_by_week[] = $calentry;
                }
            }
        }
        return $calendars_by_week;
    }
    /**
     * Compress calendars by grouping consecutive weeks with the same timetable.
     * @param array $calendars ordered by startdate. All calendars starts on monday and ends in next sunday.
     */
    public static function compress_calendars($calendars_by_week)
    {
        // Fuse identical weeks. Assume array is ordered by week.
        $calendars = [];
        $lastcalentry = null;
        $lastcalentryweek = null;
        while (count($calendars_by_week) > 0) {
            // Just check the previous calentry.
            $calentry = reset($calendars_by_week);
            $week = key($calendars_by_week);
            unset($calendars_by_week[$week]);

            if (
                $lastcalentry &&
                $week == $lastcalentryweek + 1 &&
                $calentry->timetables == $lastcalentry->timetables
            ) {
                $lastcalentry->endDate = $calentry->endDate;
            } else {
                $calendars[$week] = $calentry;
                $lastcalentry = $calentry;
            }
            $lastcalentryweek = $week;
        }

        // Fuse identical days in timetables.
        foreach ($calendars as $week => $calentry) {
            $timetables = [];
            foreach ($calentry->timetables as $timetable) {
                // Seach in timetables.
                $same = current(array_filter($timetables, function ($t) use ($timetable) {
                    return $t->startTime == $timetable->startTime &&
                        $t->endTime == $timetable->endTime &&
                        $t->info == $timetable->info;
                }));
                if ($same) {
                    if (!strpos($same->weekdays, $timetable->weekdays)) {
                        $same->weekdays .= ',' . $timetable->weekdays;
                    }
                } else {
                    $timetables[] = (object) $timetable;
                }
            }
            $calentry->timetables = array_values($timetables);
        }
        return $calendars;
    }
    /**
     * Generate a list of dates from a calendar structure.
     * iterate by weeks from startdate to enddate generating a datestart and dateend for each weekday.
     * @param mixed $calendar
     * @return void
     */
    public static function expand_dates_from_calendar($calendar): array
    {
        $dates = [];
        $start = strtotime($calendar->startDate);
        $end = strtotime($calendar->endDate);

        $startweek = date('W', $start);
        $endweek = date('W', $end);
        for ($week = $startweek; $week <= $endweek; $week++) {
            $weekstart = strtotime("monday this week",
                            strtotime(
                                date('Y', $start) . 'W' . sprintf('%02d', $week)));
            foreach ($calendar->timetables as $timetable) {
                $weekdays = explode(',', $timetable->weekdays);
                foreach ($weekdays as $weekday) {
                    // Translate weekday to strtotime weekday.
                    $weekdaynum = array_search($weekday, lib::WEEK_DAYS) + 1;
                    $date = $weekstart + ($weekdaynum - 1) * 86400;

                    if ($date <= $end) {
                        $dates[] = (object) [
                            'weekdays' => $weekday,
                            'weekdaynum' => $weekdaynum,
                            'date' => date('Y-m-d', $date),
                            'startTime' => $timetable->startTime,
                            'endTime' => $timetable->endTime,
                            'info' => $timetable->info,
                        ];
                    }
                }
            }
        }

        return $dates;

    }
    /**
     * Translate a set of weekdays with format LMXJVSD to a set of weekdays with format Mon, Tue, Wed, Thu, Fri, Sat, Sun. 
     */
    public static function translate_weekdays($weekdays) {
        $weekdays = explode(',', $weekdays);
        $translated = [];
        foreach ($weekdays as $weekday) {
            $translatedkey = array_search($weekday, lib::WEEK_DAYS_I18N);
            $translated[] = get_string($translatedkey, 'calendar');
        }
        return implode(',', $translated);        
    }
}
