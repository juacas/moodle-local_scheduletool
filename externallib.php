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

require_once ($CFG->dirroot . '/lib/externallib.php');

class local_attendancewebhook_external extends external_api
{

    public static function add_session_parameters()
    {
        return new external_function_parameters(array());
    }

    public static function add_session()
    {
        try {
            $context = context_system::instance();
            self::validate_context($context);
            if (get_config('local_attendancewebhook', 'modattendance_enabled')) {

                if (get_config('local_attendancewebhook', 'export_courses_as_topics')) {
                    require_capability('moodle/course:manageactivities', $context);
                    require_capability('mod/attendance:addinstance', $context);
                    require_capability('mod/attendance:changepreferences', $context);
                }
                require_capability('mod/attendance:manageattendances', $context);
                require_capability('mod/attendance:takeattendances', $context);
                require_capability('mod/attendance:changeattendances', $context);

                if (get_config('local_attendancewebhook', 'tempusers_enabled')) {
                    require_capability('mod/attendance:managetemporaryusers', $context); // TODO: Check conditionally.
                    require_capability('moodle/user:create', $context);
                    require_capability('moodle/user:update', $context);
                }
            }
            $event = \local_attendancewebhook\lib::get_event();
            if (!$event) {
                return false;
            }

            $config = \local_attendancewebhook\lib::get_config();
            if (!$config) {
                return false;
            }

            $errors = [];

            // If Rest services are enabled, check topicId format.
            if ($config->restservices_enabled) {
                global $DB;
                $att_target = \local_attendancewebhook\target_base::get_target($event, $config);
                $att_target->errors = &$errors;
                $att_target->register_attendances();

                if (count($errors) > 0) {
                    \local_attendancewebhook\lib::notify_error($config, $event, $errors);
                }
                return true;
            }

        } catch (Exception $e) {

            \local_attendancewebhook\lib::log_error($e);
            if ($event && $config) {
                \local_attendancewebhook\lib::notify_error($config, $event, $errors);
            }
        }
        return false;
    }
    public static function add_session_returns()
    {
        return new external_value(PARAM_BOOL);
    }
    public static function save_attendance_parameters()
    {
        return new external_function_parameters([]);
    }

    public static function save_attendance()
    {
        try {
            $context = context_system::instance();
            self::validate_context($context);
            if (get_config('local_attendancewebhook', 'mod_attendance_enabled')) {

                if (get_config('local_attendancewebhook', 'export_courses_as_topics')) {
                    require_capability('moodle/course:manageactivities', $context);
                    require_capability('mod/attendance:addinstance', $context);
                    require_capability('mod/attendance:changepreferences', $context);
                }
                require_capability('mod/attendance:manageattendances', $context);
                require_capability('mod/attendance:takeattendances', $context);
                require_capability('mod/attendance:changeattendances', $context);

                if (get_config('local_attendancewebhook', 'tempusers_enabled')) {
                    require_capability('mod/attendance:managetemporaryusers', $context); // TODO: Check conditionally.
                    require_capability('moodle/user:create', $context);
                    require_capability('moodle/user:update', $context);
                }
            }
            $event = \local_attendancewebhook\lib::get_attendance_event();
            if (!$event) {
                return false;
            }

            $config = \local_attendancewebhook\lib::get_config();
            if (!$config) {
                return false;
            }

            $errors = [];

            // If Rest services are enabled, check topicId format.
            if ($config->restservices_enabled) {
                global $DB;
                $att_target = \local_attendancewebhook\target_base::get_target($event, $config);
                $att_target->errors = &$errors;
                $att_target->register_attendances();

                if (count($errors) > 0) {
                    \local_attendancewebhook\lib::notify_error($config, $event, $errors);
                    // One error means that the attendance was not saved.
                    return false;
                }
                return true;
            }

        } catch (Exception $e) {

            \local_attendancewebhook\lib::log_error($e);
            if ($event && $config) {
                \local_attendancewebhook\lib::notify_error($config, $event, $errors);
            }
        }
        return false;
    }
    public static function save_attendance_returns()
    {
        return new external_value(PARAM_BOOL);
    }
}
