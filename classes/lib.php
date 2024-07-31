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

require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/user/lib.php');

class lib {

    const CM_IDNUMBER = 'local_attendancewebhook';

    const STATUS_DESCRIPTIONS = array(
        'UNKNOWN' => 'ASISTENCIA',
        'ON_SITE' => 'PRESENCIAL',
        'DISTANCE' => 'A DISTANCIA'
    );

    const STATUS_ACRONYMS = array(
        'UNKNOWN' => 'A',
        'ON_SITE' => 'P',
        'DISTANCE' => 'D'
    );

    public static function get_event() {
        $json = file_get_contents("php://input");
        self::log_info('Request received: '.$json);
        $object = json_decode($json);
        $event = new event($object);
        $message = 'Activity of type '.$event->get_topic()->get_type().'.';
        if ($event->get_topic()->get_type() !== 'COMMON') {
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            return $event;
        }
    }

    public static function get_config() {
        $config = get_config('local_attendancewebhook');
        if (empty($config->module_name) || !isset($config->module_section)
            || empty($config->course_id) || empty($config->member_id)
            || empty($config->user_id) || !isset($config->tempusers_enabled)
            || !isset($config->notifications_enabled)) {
            self::log_error('Plugin misconfigured: '.json_encode($config));
            return false;
        } else {
            self::log_info('Plugin configured: '.json_encode($config));
            return $config;
        }
    }

    public static function get_module() {
        global $DB;
        $params = array('name' => 'attendance');
        $module = $DB->get_record('modules', $params);
        $message = 'Module '.json_encode($params).(!$module ? ' not' : '').' found.';
        !$module ? self::log_error($message) : self::log_info($message);
        return $module;
    }

    public static function get_course($config, $event) {
        global $DB;
        $params = array($config->course_id => $event->get_topic()->get_topic_id());
        $courses = $DB->get_records('course', $params);
        $message = count($courses).' course(s) '.json_encode($params).' found.';
        if (count($courses) != 1) {
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            return $courses[array_keys($courses)[0]];
        }
    }

    public static function get_course_module($config, $course, $module) {
        global $DB;
        $params = array('course' => $course->id, 'module' => $module->id, 'idnumber' => self::CM_IDNUMBER);
        $cms = $DB->get_records('course_modules', $params);
        $message = count($cms).' course modules(s) '.json_encode($params).' found.';
        if (count($cms) > 1) {
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            if (count($cms) == 1) {
                $cm = $cms[array_keys($cms)[0]];
                $params = array('id' => $cm->instance);
                $attendance = $DB->get_record('attendance', $params);
                if ($attendance->name != $config->module_name) {
                    self::log_info('Module name modified.');
                    set_coursemodule_name($cm->id, $config->module_name);
                    self::log_info('Module name updated.');
                }
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
                create_module($moduleinfo);
                self::log_info('Course module created.');
                $cm = $DB->get_record('course_modules', $params);
                $DB->delete_records('attendance_statuses', array('attendanceid' => $cm->instance));
                foreach (self::STATUS_DESCRIPTIONS as $name => $description) {
                    $status = new \stdClass();
                    $status->attendanceid = $cm->instance;
                    $status->acronym = self::STATUS_ACRONYMS[$name];
                    $status->description = $description;
                    $status->id = $DB->insert_record('attendance_statuses', $status);
                    self::log_info('Attendance status "'.$status->description.'" created.');
                }
                return $cm;
            }
        }
    }

    public static function get_session($cm, $event) {
        global $DB;
        $params = array('attendanceid' => $cm->instance, 'sessdate' => $event->get_opening_time());
        $sessions = $DB->get_records('attendance_sessions', $params);
        $message = count($sessions).' attendance session(s) '.json_encode($params).' found.';
        if (count($sessions) > 1) {
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            if (count($sessions) == 1) {
                $session = $sessions[array_keys($sessions)[0]];
                $session->lasttaken = $event->get_closing_time();
                $session->timemodified = time();
                $DB->update_record('attendance_sessions', $session);
                self::log_info('Attendance session updated.');
                return $session;
            } else {
                $session = new \stdClass();
                $session->attendanceid = $cm->instance;
                $session->sessdate = $event->get_opening_time();
                $session->lasttaken = $event->get_closing_time();
                $session->description = $event->get_event_note();
                $session->timemodified = time();
                $session->id = $DB->insert_record('attendance_sessions', $session);
                self::log_info('Attendance session created.');
                return $session;
            }
        }
    }

    public static function get_user_enrol($config, $attendance, $course) {
        global $DB;
        $sql = "SELECT u.* FROM {user} u" .
            " JOIN {user_enrolments} ue ON ue.userid = u.id" .
            " JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)" .
            " WHERE u." . $config->user_id . " = :" . $config->user_id;
        $params = array($config->user_id => self::get_member_id($config, $attendance->get_member()), 'courseid' => $course->id);
        $users = $DB->get_records_sql($sql, $params);
        $message = count($users).' course user(s) '.json_encode($params).' found.';
        self::log_info($message);
        if (count($users) != 1) {
            return false;
        } else {
            return $users[array_keys($users)[0]];
        }
    }

    private static function get_member_id($config, $member) {
        if ($config->member_id === 'username') {
            return $member->get_username();
        } else if ($config->member_id === 'email') {
            return $member->get_email();
        } else {
            return '';
        }
    }

    public static function is_tempusers_enabled($config) {
        if (!$config->tempusers_enabled) {
            self::log_info('Temporary users disabled.');
            return false;
        } else {
            self::log_info('Temporary users enabled.');
            return true;
        }
    }

    public static function get_tempuser($attendance, $course) {
        global $DB;
        $params = array('email' => $attendance->get_member()->get_email(), 'courseid' => $course->id);
        $tempusers = $DB->get_records('attendance_tempusers', $params);
        $message = count($tempusers).' attendance temporary user(s) '.json_encode($params).' found.';
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
                    $user->username = uniqid().'@'.self::CM_IDNUMBER;
                    $params = array('username' => $user->username);
                    $found = $DB->get_record('user', $params);
                } while ($found);
                $user->email = $user->username;
                $user->id = user_create_user($user, false, false);
                $user->deleted = 1;
                user_update_user($user, false, false);
                self::log_info('User '.$user->username.' created.');
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

    public static function get_status($cm, $attendance) {
        global $DB;
        $params = array('attendanceid' => $cm->instance, 'description' => self::STATUS_DESCRIPTIONS[$attendance->get_mode()]);
        $statuses = $DB->get_records('attendance_statuses', $params);
        $message = count($statuses).' attendance statuses '.json_encode($params).' found.';
        if (count($statuses) != 1) {
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            return $statuses[array_keys($statuses)[0]];
        }
    }

    public static function get_log($session, $user, $tempuser, $status, $attendance) {
        global $DB;
        $params = array('sessionid' => $session->id, 'studentid' => $user ? $user->id : $tempuser->studentid);
        $logs = $DB->get_records('attendance_log', $params);
        $message = count($logs).' attendance log(s) '.json_encode($params).' found.';
        if (count($logs) > 1) {
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            if (count($logs) == 1) {
                return $logs[array_keys($logs)[0]];
            } else {
                $log = new \stdClass();
                $log->sessionid = $session->id;
                $log->studentid = $user ? $user->id : $tempuser->studentid;
                $log->statusid = $status->id;
                $log->timetaken = $attendance->get_server_time();
                $log->remarks = $attendance->get_attendance_note();
                $log->id = $DB->insert_record('attendance_log', $log);
                self::log_info('Attendance log created.');
                return $log;
            }
        }
    }

    public static function log_info($message) {
        self::log($message, 'INFO');
    }

    public static function log_error($message) {
        self::log($message, 'ERROR');
    }

    private static function log($message, $type) {
        global $CFG;
        $dir = $CFG->dataroot.DIRECTORY_SEPARATOR.self::CM_IDNUMBER.DIRECTORY_SEPARATOR.'logs';
        if (!file_exists($dir) && !mkdir($dir, 0777, true)) {
            return;
        }
        $file = $dir.DIRECTORY_SEPARATOR.'trace.log';
        $maxcount = 10;
        $maxsize = 5000000; // 5MB en bytes.
        if (file_exists($file) && filesize($file) >= $maxsize) {
            $oldest = $file.".".$maxcount;
            if (file_exists($oldest)) {
                unlink($oldest);
            }
            for ($i = $maxcount; $i > 0; $i--) {
                $current = $file.".".$i;
                if (file_exists($current)) {
                    $next = $file.".".($i + 1);
                    rename($current, $next);
                }
            }
            rename($file, $file.".1");
        }
        file_put_contents($file, date('Y-m-d H:i:s').' '.$type.' '.$message."\n", FILE_APPEND);
    }

    public static function notify_error($config, $event, $attendances = null) {
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
            $message->subject = 'Moodle '.$config->module_name.': '.get_string('notification_subject', 'local_attendancewebhook');
            if ($attendances) {
                $text = get_string('notification_error_attendances', 'local_attendancewebhook');
            } else {
                $text = get_string('notification_error_event', 'local_attendancewebhook');
            }
            $message->fullmessage = $text.' '.strval($event).'.';
            $message->fullmessagehtml = '<p>'.$text.'</p><p>'.strval($event).'</p>';
            if ($attendances) {
                $message->fullmessage .= ' '.get_string('notification_attendances', 'local_attendancewebhook');
                foreach ($attendances as &$attendance) {
                    $message->fullmessage .= ' '.strval($attendance).',';
                    $message->fullmessagehtml .= '<p>'.strval($attendance).'</p>';
                }
                $message->fullmessage = substr($message->fullmessage, 0, strlen($message->fullmessage) - 1).'.';
            }
            $text = get_string('notification_contact_admin', 'local_attendancewebhook');
            $message->fullmessage .= ' '.$text;
            $message->fullmessagehtml .= '<p>'.$text.'</p>';
            $message->fullmessageformat = FORMAT_HTML;
            $message->smallmessage = $message->fullmessage;
            $message->notification = 1;
            message_send($message);
            self::log_info('Notification sent: '.$message->fullmessagehtml);
        }
    }

    private static function get_user($config, $member) {
        global $DB;
        $params = array($config->user_id => self::get_member_id($config, $member));
        $users = $DB->get_records('user', $params);
        $message = count($users).' users(s) '.json_encode($params).' found.';
        if (count($users) != 1) {
            self::log_error($message);
            return false;
        } else {
            self::log_info($message);
            return $users[array_keys($users)[0]];
        }
    }

}
