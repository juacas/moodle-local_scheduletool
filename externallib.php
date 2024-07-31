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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/externallib.php');

class local_attendancewebhook_external extends external_api {

    public static function add_session_parameters() {
        return new external_function_parameters(array());
    }

    public static function add_session() {
        try {

            $context = context_system::instance();
            self::validate_context($context);
            require_capability('moodle/course:manageactivities', $context);
            require_capability('mod/attendance:addinstance', $context);
            require_capability('mod/attendance:changepreferences', $context);
            require_capability('mod/attendance:manageattendances', $context);
            require_capability('mod/attendance:takeattendances', $context);
            require_capability('mod/attendance:changeattendances', $context);
            require_capability('mod/attendance:managetemporaryusers', $context);
            require_capability('moodle/user:create', $context);
            require_capability('moodle/user:update', $context);

            $event = \local_attendancewebhook\lib::get_event();
            if (!$event) {
                return false;
            }

            $config = \local_attendancewebhook\lib::get_config();
            if (!$config) {
                return false;
            }

            $module = \local_attendancewebhook\lib::get_module();
            if (!$module) {
                \local_attendancewebhook\lib::notify_error($config, $event);
                return false;
            }

            $course = \local_attendancewebhook\lib::get_course($config, $event);
            if (!$course) {
                \local_attendancewebhook\lib::notify_error($config, $event);
                return false;
            }

            $cm = \local_attendancewebhook\lib::get_course_module($config, $course, $module);
            if (!$cm) {
                \local_attendancewebhook\lib::notify_error($config, $event);
                return false;
            }

            $session = \local_attendancewebhook\lib::get_session($cm, $event);
            if (!$session) {
                \local_attendancewebhook\lib::notify_error($config, $event);
                return false;
            }

            $errors = array();
            foreach ($event->get_attendances() as &$attendance) {
                $user = \local_attendancewebhook\lib::get_user_enrol($config, $attendance, $course);
                if (!$user) {
                    if (!\local_attendancewebhook\lib::is_tempusers_enabled($config)) {
                        continue;
                    } else {
                        $tempuser = \local_attendancewebhook\lib::get_tempuser($attendance, $course);
                        if (!$tempuser) {
                            $errors[] = $attendance;
                            continue;
                        }
                    }
                }

                $status = \local_attendancewebhook\lib::get_status($cm, $attendance);
                if (!$status) {
                    $errors[] = $attendance;
                    continue;
                }

                $log = \local_attendancewebhook\lib::get_log($session, $user, $tempuser, $status, $attendance);
                if (!$log) {
                    $errors[] = $attendance;
                }
            }

            if (count($errors) > 0) {
                \local_attendancewebhook\lib::notify_error($config, $event, $errors);
            }
            return true;

        } catch (Exception $e) {

            \local_attendancewebhook\lib::log_error($e);
            if ($event && $config) {
                \local_attendancewebhook\lib::notify_error($config, $event, $errors);
            }
            return false;
        }
    }

    public static function add_session_returns() {
        return new external_value(PARAM_BOOL);
    }

}
