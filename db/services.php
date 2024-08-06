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

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_attendancewebhook_add_session' => [
        'classname' => 'local_attendancewebhook_external',
        'methodname' => 'add_session',
        'classpath' => 'local/attendancewebhook/externallib.php',
        'description' => 'Add a full attendance session with a list of attendees.',
        'type' => 'write'
    ],
    'local_attendancewebhook_save_attendance' => [
        'classname' => 'local_attendancewebhook_external',
        'methodname' => 'save_attendance',
        'classpath' => 'local/attendancewebhook/externallib.php',
        'description' => 'Save attendance data.',
        'type' => 'write'
    ]
];

$services = [
    'Attendance Webhook' => [
        'functions' => [
            'local_attendancewebhook_add_session',
            'local_attendancewebhook_save_attendance',
        ],
        'restrictedusers' => 0,
        'enabled' => 0,
        'shortname' => 'attendancewebhook'
    ]
];
