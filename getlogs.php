<?php
require_once(__DIR__ . '/../../config.php');

// Download log file.
$dir = $CFG->dataroot . DIRECTORY_SEPARATOR . \local_attendancewebhook\lib::CM_IDNUMBER . DIRECTORY_SEPARATOR . 'logs';
$file = $dir . DIRECTORY_SEPARATOR . 'trace.log';
// Flush the file to the browser.
header('Content-Type: text/plain');

if (file_exists($file)) {
    readfile($file);
} else {
    echo 'No log file found.';
}
