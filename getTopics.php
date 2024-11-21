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
 * Give the attendance-abled events to the app.
 * Return a JSON with the events that are suitable to attendance marking:
 * - Courses: as original version.
 * - Attendance sessions: topicId encoded with course-module-session.
 * - HybridTeaching sessions: topicId encoded with course-module-session. (TODO)
 *
 * • topicId: Identificador único de la asignatura. Es un campo obligatorio y es muy importante
 *   que sea único, no puede haber varias asignaturas con el mismo identificador para un 
 *   organizador.
 *   • name: Nombre de la asignatura. Es un campo obligatorio si no se envía el campo names.
 *   • names: Nombre de la asignatura en varios idiomas. Este campo es obligatorio si no se 
 *   envía el campo anterior name.
 *   • info: información adicional al nombre de la asignatura que permite al organizador 
 *   identificar sin ningún género de dudas la asignatura al incluir la titulación, el grupo, el curso, 
 *   etc. Es un campo obligatorio, aunque su contenido puede estar vacío.
 *   • infos: información adicional de la asignatura en varios idiomas. Es obligatorio si no se 
 *   envía el campo anterior info.
 *   • calendar: objeto opcional con los horarios de la asignatura y los siguientes campos:
 *   o startDate: fecha inicial del curso o del período en el que aplican los horarios. El 
 *   formato de la fecha debe ser YYYY-MM-DD.
 *   o endDate: fecha final del curso o del período en el que aplican los horarios. El 
 *   formato de la fecha debe ser YYYY-MM-DD.
 *   o timetables: array de horarios y que contienen los siguientes campos:
 *   § weekdays: días de la semana en que se aplica el horario, indicando sus 
 *   iniciales en mayúsculas y separados por comas, con excepción del 
 *   miércoles al que le corresponde una “X”
 *   § startTime: hora inicial de la actividad
 *   § endTime: hora final de la actividad
 *   § info: información adicional donde se puede añadir información del aula o 
 *   cualquier otro dato pertinente.
 *   § infos: información adicional del horario en varios idiomas. Es obligatorio 
 *   si se envía un horario y no se envía el campo anterior info.
 *   • externalIntegration: booleano que indica si la actividad debe ser integrada con algún 
 *   sistema externo, como por ejemplo Moodle. Si su valor es true las asistencias a la actividad 
 *   se enviarán a los sistemas externos con los que se haya integrado la aplicación. Si su 
 *   valor es false esas asistencias no se enviarán al sistema externo. Si no se devuelve en la 
 *   llamada se asume que su valor es true.
 *   • tag: etiqueta asociada a la actividad que puede servir posteriormente para agrupar 
 *   actividades o para filtrar las asistencias. Cada etiqueta puede tener un significado diferente 
 *   en cada instalación. Por ejemplo, cada etiqueta podría representar el centro donde se 
 *   AppCRUE – Control de asistencia. API de integración 
 *   REST 7
 *   imparte la asignatura, el departamento, la titulación, etc. Su valor no debe incluir espacios 
 *   en blanco. Es un parámetro opcional.
 * @package    local_scheduletool
 * @copyright  2021 University of Valladoild, Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_scheduletool;
use local_scheduletool_external;
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/filelib.php');
/** @var \moodle_database $DB */
global $DB;

if (!get_config('local_scheduletool', 'restservices_enabled')) {
    header('HTTP/1.1 405 Method Not Allowed');
    die();
    // Better act as a service don't throw new moodle_exception('servicedonotexist', 'error').
}
try {
    $apikey = required_param('apikey', PARAM_ALPHANUMEXT);
    $apiuser = required_param('apiuser', PARAM_ALPHANUMEXT);
    $userid = required_param('userid', PARAM_ALPHANUMEXT);
} catch (\moodle_exception $e) {
    header('HTTP/1.0 401 Bad Request');
    die();
}

$PAGE->set_context(null);
header('Content-Type: application/json;charset=UTF-8');
lib::log_info('Request getTopics:' . 'User: ' . $userid . ' Apikey: ' . $apikey . ' Apiuser: ' . $apiuser. '===========================');
// Check apikey and apiuser aginst config.
if ($apikey != get_config('local_scheduletool', 'apikey') || $apiuser != get_config('local_scheduletool', 'apiuser')) {
    header('HTTP/1.0 401 Unauthorized');
    die();
}
// Find userid. TODO: Bypass this check if user is in remotes. Use getUserData()
$useridfield = get_config('local_scheduletool', 'user_id');
$user = $DB->get_record('user', [$useridfield => $userid], '*');

// if (!$user) {
//     header('HTTP/1.0 404 Not Found');
//     die();
// }
$topics = [];
$local_topics = [];
$fromdatestr = optional_param('fromdate', null, PARAM_TEXT);
$todatestr = optional_param('todate', null , PARAM_TEXT);
$fromdate = $fromdatestr ? strtotime($fromdatestr): null;
$todate = $todatestr ? strtotime($todatestr) : null;

try {
    if ($user) {
        $compress = get_config(plugin: 'local_scheduletool', name: 'compact_calendar');
        $local_topics = lib::get_local_topics($user, compress: $compress);
    }
    // Get  proxyes topics.
    $remote_topics = lib::get_remote_topics($userid);
    // Fuse topics.
    $topics = array_merge($local_topics, $remote_topics);
    
    $response = [ "topics" => $topics ];
    $response = json_encode($response, JSON_HEX_QUOT | JSON_PRETTY_PRINT);
    lib::log_info('Response getTopics: ' . $response);    
    echo $response;
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error: ' . $e->getMessage());
    die();
}
