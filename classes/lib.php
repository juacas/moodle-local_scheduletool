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
use PhpXmlRpc\Exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/user/lib.php');

class lib
{

    const CM_IDNUMBER = 'local_attendancewebhook';
    // TODO: i18n status descriptions.
    const STATUS_DESCRIPTIONS = array(
        'NOTPRESENT' => 'NO PRESENTADO',
        'UNKNOWN' => 'ASISTENCIA',
        'ON_SITE' => 'PRESENCIAL',
        'DISTANCE' => 'A DISTANCIA'
    );

    const STATUS_ACRONYMS = array(
        'NOTPRESENT'  => 'NP', 
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
            $message->component = 'local_attendancewebhook';
            $message->userfrom = \core_user::get_noreply_user();
            $message->courseid = $courseid;
            $message->userto = $user; // TODO: $user->lang should set the language.
            $message->name = 'notification'; // Provider local_attendancewebhook/notification. It's the only one declared.
            
            $eventstr = strval($event);
            $a = (object) ['event' => $event,
                            'module' => $config->module_name,
                            'topic' => $event->getTopic(),
                            'opening_time' => date('d-m-Y H:i', $event->get_opening_time()),
                            'closing_time' => date('d-m-Y H:i', $event->get_closing_time()),
                            'eventstr' => $eventstr,
                        ];

            $message->subject = get_string('notification_subject', 'local_attendancewebhook', $a);
            if ($level == \core\output\notification::NOTIFY_ERROR) {
                if ($attendances) {
                    $text = get_string('notification_error_attendances', 'local_attendancewebhook', $a);
                } else {
                    $text = get_string('notification_error_event', 'local_attendancewebhook', $a);
                }
                $admintext = get_string('notification_contact_admin', 'local_attendancewebhook', $a);
            } else {
                $text = get_string('notification_info', 'local_attendancewebhook', $a);
                $admintext = '';
            }
            
            $message->fullmessage = "{$text}\n";
            $message->fullmessagehtml = "<p>{$text}</p>";
            if ($attendances) {
                $message->fullmessage .= get_string('notification_messages', 'local_attendancewebhook') . "\n";
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
    public static function get_remote_topics($userid)
    {
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
// ORGANISER: users with capability to mod/attendance:takeattendances in any allowed course.
// ATTENDEE: users without it.
// TODO: Check mod/hybridteaching:createsessions
        $user->rol = 'ATTENDEE';

        $attendancecourses = get_user_capability_course('mod/attendance:takeattendances', $user->id);
        if ($attendancecourses) {
            $attendancecourses = array_filter($attendancecourses, function ($course) {
                return lib::is_course_allowed($course->id);
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
        $remotes = get_config('local_attendancewebhook', $type);
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
        $remotes = \local_attendancewebhook\lib::get_remotes('restservices_signUp');
        $event = \local_attendancewebhook\lib::get_attendance_event();
        return self::process_register_attendance_with_proxys($event, $remotes);
    }
     /**
     * Process add_session, CloseEvent request.
     * @return bool|array True if success. Array of errors otherwise.
     */
    public static function process_add_session()
    {
        $remotes = \local_attendancewebhook\lib::get_remotes('restservices_closeEvent');
        $event = \local_attendancewebhook\lib::get_event();
        return self::process_register_attendance_with_proxys($event, $remotes);
    }
    /**
     * Process register_attendance request.
     * @param $event \local_attendancewebhook\attendance_event|\local_attendancewebhook\attendance_event
     * @param $remotes array of remotes for the service.
     * @see \local_attendancewebhook\lib::get_remotes
     * @return bool|array True if success. Array of errors otherwise.
     */
    public static function process_register_attendance_with_proxys($event, $remotes) {
        if (!$event) {
            return false;
        }
        
        $config = \local_attendancewebhook\lib::get_config();
        if (!$config) {
            return false;
        }
        $att_target = null;

        try {
            $errors = [];
            $att_target = \local_attendancewebhook\target_base::get_target($event, $config);
            $att_target->errors = &$errors;
            // If Rest services are enabled, check topicId format.
            if ($config->restservices_enabled) {
                global $DB, $CFG, $USER;

                $logtaker = \local_attendancewebhook\lib::get_user($config, $event->get_logtaker());
                if (!$logtaker) {
                    throw new \Exception('Unknown logtaker:' . $event->get_logtaker());
                }
              

                
                $prefix = $att_target->prefix;
                // Get proxies.

                if ($prefix == $config->restservices_prefix) {
                    // Impersonates the logtaker user.
                    $USER = $logtaker;
                    force_current_language($logtaker->lang);
                    // Local request.
                    $att_target->register_attendances();
                } else if (isset($remotes[$prefix])) {
                    $request_url = $remotes[$prefix];
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
                        $errors[] = 'Error getting topics from ' . $request_url . ': ' . curl_error($curl);
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
            lib::log_error($errors);
            \local_attendancewebhook\lib::notify($config, $event, $att_target->getCourse()->id, \core\output\notification::NOTIFY_ERROR, $errors);
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

    //         $config = \local_attendancewebhook\lib::get_config();
    //         if (!$config) {
    //             return false;
    //         }
    //         $errors = [];
    //         // If Rest services are enabled, load controller for topicId type.
    //         if ($config->restservices_enabled) {
    //             // Impersonates the logtaker user.
    //             global $USER;
    //             $logtaker = \local_attendancewebhook\lib::get_user($config, $event->get_logtaker());
    //             if (!$logtaker) {
    //                 throw new \Exception('Unknown logtaker:' . $event->get_logtaker());
    //             }
    //             $USER = $logtaker;


    //             list($type, $prefix, $topicId) = \local_attendancewebhook\target_base::parse_topic_id($event->getTopic()->get_topic_id());
    //             if ($prefix == $config->restservices_prefix) {
    //                 // Local request.
    //                 $att_target = \local_attendancewebhook\target_base::get_target($event, $config);
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
    //         \local_attendancewebhook\lib::notify_error($config, $event, $errors);
    //         // One error means that the attendance was not saved.
    //         return $errors;
    //     } else {
    //         return true;
    //     }
    // }
    /**
     * Get allowed course categories.
     * @return array|bool of allowed category ids. True if all categories are allowed.
     */
    public static function get_allowed_categories()
    {
        $categories = get_config('local_attendancewebhook', 'enableincategories');
        if (!empty($categories)) {
            $categories = explode(',', $categories);
            $categories = array_map('intval', $categories);
            $subcats = [];
            foreach ($categories as $category) {
                $cat = \core_course_category::get($category);
                $children = $cat->get_children(); // Its cached so no problem.
                $subcats = array_merge($subcats, array_keys($children));
            }
            $categories = array_merge($categories, $subcats);
            return $categories;
        } else {
            return true;
        }
    }
    /**
     * Check if a course is allowed.
     * @param $courseid int Course id.
     * @return \stdClass|bool Course object if allowed. False otherwise.
     */
    public static function is_course_allowed($courseid)
    {
        global $DB;
        $categories = \local_attendancewebhook\lib::get_allowed_categories();
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        if ($course && ($categories === True || in_array($course->category, $categories))) {
            return $course;
        } else {
            return false;
        }
    }
}
