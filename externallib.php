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

require_once($CFG->dirroot . '/lib/externallib.php');

class local_attendancewebhook_external extends external_api
{

    public static function add_session_parameters()
    {
        return new external_function_parameters(array());
    }

    public static function add_session()
    {
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
        return local_attendancewebhook\lib::process_add_session();
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
        return local_attendancewebhook\lib::process_save_attendance();
    }
    public static function save_attendance_returns()
    {
        return new external_value(PARAM_BOOL);
    }
}
