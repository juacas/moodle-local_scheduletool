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

/**
 * SignUp. Report an individual attendance.
 *
 * @package    local_attendancewebhook
 * @copyright  2024 University of Valladoild, Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');
/** @var moodle_database $DB */
global $DB;

if (!get_config('local_attendancewebhook', 'restservices_enabled')) {
    header('HTTP/1.1 405 Method Not Allowed');
    die();
    // Better act as a service don't throw new moodle_exception('servicedonotexist', 'error').
}
try {
    $apikey = optional_param('apikey', null, PARAM_ALPHANUMEXT);
    if (empty($apikey)) {
        // Get the apikey from athorization header.   
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $apikey = $headers['Authorization'];
        }
    }


    $PAGE->set_context(null);
    header('Content-Type: application/json;charset=UTF-8');
    \local_attendancewebhook\lib::log_info("Signup Request. ===============================");
    // Check apikey and apiuser aginst config.
    if ($apikey != get_config('local_attendancewebhook', 'apikey')) {
        header('HTTP/1.0 401 Unauthorized');
        local_attendancewebhook\lib::log_error("Bad apikey: $apikey.");
        die();
    }
    // Close_event is actually an add_session web service request.
    $response = local_attendancewebhook\lib::process_save_attendance();
    if ($response == 1) {
        echo 'true';
    } else {
        echo json_encode($response);
        local_attendancewebhook\lib::log_error("Inusual response." . json_encode($response));
    }
} catch (\Exception $e) {
    header('HTTP/1.0 400 Bad Request');
    local_attendancewebhook\lib::log_error($e->getMessage());
    die();
}