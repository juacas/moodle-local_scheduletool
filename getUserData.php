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
 * Give user data to the app.
 *
 * @package    local_attendancewebhook
 * @copyright  2024 University of Valladoild, Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_attendancewebhook;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');
/** @var \moodle_database $DB */
global $DB;

if (!get_config('local_attendancewebhook', 'restservices_enabled')) {
    header('HTTP/1.1 405 Method Not Allowed');
    die();
    // Better act as a service don't throw new moodle_exception('servicedonotexist', 'error').
}
try {
    $apikey = required_param('apikey', PARAM_ALPHANUMEXT);
    $apiuser = required_param('apiuser', PARAM_ALPHANUMEXT);
    $userid = required_param('userid', PARAM_ALPHANUMEXT);
} catch (\moodle_exception $e) {
    header('HTTP/1.0 400 Bad Request');
    die();
}

$PAGE->set_context(null);
header('Content-Type: application/json;charset=UTF-8');

// Check apikey and apiuser aginst config.
if ($apikey != get_config('local_attendancewebhook', 'apikey') || $apiuser != get_config('local_attendancewebhook', 'apiuser')) {
    header('HTTP/1.0 401 Unauthorized');
    die();
}
lib::log_info('Request getUserData:' . 'User: ' . $userid . ' Apikey: ' . $apikey . ' Apiuser: ' . $apiuser . '===========================');
// Find userid.
$user_data = lib::get_user_data($userid);
$remote_user_data = lib::get_user_data_remote($userid);
// User not found.
if (!$user_data && count($remote_user_data) == 0) {
    header('HTTP/1.0 404 Not Found');
    die();
}
// Merge user data from local and remote selecting users with type "ORGANIZER" first.
foreach ($remote_user_data as $remote_user) {
    if ($remote_user->rol == 'ORGANISER') {
        if ($user_data)
            $user_data = $remote_user;
        break;
    }
    if (!$user_data) {
        $user_data = $remote_user;
    }
}
if ($user_data) {
    // Check if user is authorized.
    $authorized_users = trim(get_config('local_attendancewebhook', 'authorized_users'));
    if ($authorized_users != '' && !str_contains(get_config('local_attendancewebhook', 'authorized_users'), $user_data->email)) {
        header('HTTP/1.0 401 Unauthorized');
        die();
    }
    // Get authorized organizers.
    $authorized_organizers = trim(get_config('local_attendancewebhook', 'authorized_organizers'));
    if ($authorized_organizers != '' && !str_contains($authorized_organizers, $user_data->email)) {
        $user_data->rol = 'ATTENDEE';
    }
}
$response = json_encode($user_data, JSON_HEX_QUOT | JSON_PRETTY_PRINT);

echo $response;
