<?php
require_once(__DIR__ . '/../../config.php');
if (!get_config('local_scheduletool', 'restservices_enabled') || !get_config('local_scheduletool', 'logs_enabled')) {
    header('HTTP/1.1 405 Method Not Allowed');
    die();
    // Better act as a service don't throw new moodle_exception('servicedonotexist', 'error').
}
try {
    $apikey = required_param('apikey', PARAM_ALPHANUMEXT);

} catch (moodle_exception $e) {
    header('HTTP/1.0 400 Bad Request');
    die();
}
// Check apikey and apiuser aginst config.
if ($apikey != get_config('local_scheduletool', 'apikey') ) {
    header('HTTP/1.0 401 Unauthorized');
    die();
}
// Download log file.
$dir = $CFG->dataroot . DIRECTORY_SEPARATOR . \local_scheduletool\lib::CM_IDNUMBER . DIRECTORY_SEPARATOR . 'logs';
$file = $dir . DIRECTORY_SEPARATOR . 'trace.log';
// Flush the file to the browser.
header('Content-Type: text/plain');

if (file_exists($file)) {
    readfile($file);
} else {
    echo 'No log file found.';
}
