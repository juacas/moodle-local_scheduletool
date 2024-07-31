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

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_attendancewebhook', new lang_string('pluginname', 'local_attendancewebhook'));

    $ADMIN->add('localplugins', $settings);

    $settings->add(
        new admin_setting_configtext(
            'local_attendancewebhook/module_name',
            new lang_string('module_name_name', 'local_attendancewebhook'),
            new lang_string('module_name_description', 'local_attendancewebhook'),
            new lang_string('pluginname', 'local_attendancewebhook'),
            PARAM_TEXT,
            64
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_attendancewebhook/module_section',
            new lang_string('module_section_name', 'local_attendancewebhook'),
            new lang_string('module_section_description', 'local_attendancewebhook'),
            0,
            PARAM_INT,
            64
        )
    );

    $settings->add(
        new admin_setting_configselect(
            'local_attendancewebhook/course_id',
            new lang_string('course_id_name', 'local_attendancewebhook'),
            new lang_string('course_id_description', 'local_attendancewebhook'),
            'shortname',
            array('shortname' => 'shortname', 'idnumber' => 'idnumber')
        )
    );

    $settings->add(
        new admin_setting_configselect(
            'local_attendancewebhook/user_id',
            new lang_string('user_id_name', 'local_attendancewebhook'),
            new lang_string('user_id_description', 'local_attendancewebhook'),
            'username',
            array('username' => 'username', 'email' => 'email')
        )
    );

    $settings->add(
        new admin_setting_configselect(
            'local_attendancewebhook/member_id',
            new lang_string('member_id_name', 'local_attendancewebhook'),
            new lang_string('member_id_description', 'local_attendancewebhook'),
            'username',
            array('username' => 'username', 'email' => 'email')
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'local_attendancewebhook/tempusers_enabled',
            new lang_string('tempusers_enabled_name', 'local_attendancewebhook'),
            new lang_string('tempusers_enabled_description', 'local_attendancewebhook'),
            0
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'local_attendancewebhook/notifications_enabled',
            new lang_string('notifications_enabled_name', 'local_attendancewebhook'),
            new lang_string('notifications_enabled_description', 'local_attendancewebhook'),
            0
        )
    );

}
