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

namespace local_attendancewebhook\external;

defined('MOODLE_INTERNAL') || die;
use \core_external\external_api;
use \core_external\external_function_parameters;
use \core_external\external_value;
use \local_attendancewebhook\lib;

require_once($CFG->dirroot . '/lib/externallib.php');
/**
 * @deprecated Moved to plain REST php scripts.
 */
class close_event extends external_api
{

    public static function execute_parameters()
    {
        return new external_function_parameters(array());
    }

    public static function execute()
    {
        $context = \context_system::instance();
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
        return lib::process_add_session();
    }
     /**
     * Mark the function as deprecated.
     * @return bool
     */
    public static function execute_is_deprecated() {
        return true;
    }
    public static function execute_return()
    {
        return new external_value(PARAM_BOOL);
    }
}
