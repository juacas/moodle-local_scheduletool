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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/filelib.php');
/** @var moodle_database $DB */
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
} catch (moodle_exception $e) {
    header('HTTP/1.0 401 Bad Request');
    die();
}

$PAGE->set_context(null);
header('Content-Type: text/json; charset=utf-8');

// Check apikey and apiuser aginst config.
if ($apikey != get_config('local_attendancewebhook', 'apikey') || $apiuser != get_config('local_attendancewebhook', 'apiuser')) {
    header('HTTP/1.0 401 Unauthorized');
    die();
}
// Find userid.
$useridfield = get_config('local_attendancewebhook', 'restservices_field_userid');
$user = $DB->get_record('user', [$useridfield => $userid], '*');

if (!$user) {
    header('HTTP/1.0 404 Not Found');
    die();
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

if (count($attendancecourses) > 0) {
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

$response = json_encode($userresponse, JSON_HEX_QUOT | JSON_PRETTY_PRINT);

echo $response;
