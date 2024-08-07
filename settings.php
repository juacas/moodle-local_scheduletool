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

    // If mod_attendance is installed, show the following settings.
    $pluginmgr = \core_plugin_manager::instance();
    $attplugin = $pluginmgr->get_plugin_info('mod_attendance');
    $attplugin_available = $attplugin !== null && $attplugin->is_enabled();
    $hybplugin = $pluginmgr->get_plugin_info('mod_hybridteaching');
    $hybplugin_available = $hybplugin !== null && $hybplugin->is_enabled();

    // mod_attendance section heading.
    $settings->add(
        new admin_setting_heading(
            'local_attendancewebhook/modattendance_heading',
            new lang_string('modattendance_heading', 'local_attendancewebhook'),
            new lang_string('modattendance_description', 'local_attendancewebhook')
        )
    );
    // Settings for mod_attendance integration.
    if ($attplugin_available) {
        $settings->add(
            new admin_setting_configcheckbox(
                'local_attendancewebhook/modattendance_enabled',
                new lang_string('modattendance_enabled_name', 'local_attendancewebhook'),
                new lang_string('modattendance_enabled_description', 'local_attendancewebhook'),
                $attplugin_available
            )
        );

        // Checkbox for exporting mod_attendance sessions as topics.
        $settings->add(
            new admin_setting_configcheckbox(
                'local_attendancewebhook/export_sessions_as_topics',
                new lang_string('export_sessions_as_topics_name', 'local_attendancewebhook'),
                new lang_string('export_sessions_as_topics_description', 'local_attendancewebhook'),
                1
            )
        );
        $settings->hide_if('local_attendancewebhook/export_sessions_as_topics', 'local_attendancewebhook/modattendance_enabled', 'notchecked');
        // Checkbox for exporting courses as topics.
        $settings->add(
            new admin_setting_configcheckbox(
                'local_attendancewebhook/export_courses_as_topics',
                new lang_string('export_courses_as_topics_name', 'local_attendancewebhook'),
                new lang_string('export_courses_as_topics_description', 'local_attendancewebhook'),
                1
            )
        );
        $settings->hide_if('local_attendancewebhook/export_courses_as_topics', 'local_attendancewebhook/modattendance_enabled', 'notchecked');
        // Setting for creating a new instance for course topics.
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
        $settings->hide_if('local_attendancewebhook/module_name', 'local_attendancewebhook/modattendance_enabled', 'notchecked');
        $settings->hide_if('local_attendancewebhook/module_name', 'local_attendancewebhook/export_courses_as_topics', 'notchecked');

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
        $settings->hide_if('local_attendancewebhook/module_section', 'local_attendancewebhook/modattendance_enabled', 'notchecked');
        $settings->hide_if('local_attendancewebhook/module_section', 'local_attendancewebhook/export_courses_as_topics', 'notchecked');

        $settings->add(
            new admin_setting_configselect(
                'local_attendancewebhook/course_id',
                new lang_string('course_id_name', 'local_attendancewebhook'),
                new lang_string('course_id_description', 'local_attendancewebhook'),
                'shortname',
                array('shortname' => 'shortname', 'idnumber' => 'idnumber')
            )
        );
        $settings->hide_if('local_attendancewebhook/course_id', 'local_attendancewebhook/modattendance_enabled', 'notchecked');
        $settings->hide_if('local_attendancewebhook/course_id', 'local_attendancewebhook/export_courses_as_topics', 'notchecked');
        // Temporary users.
        $settings->add(
            new admin_setting_configcheckbox(
                'local_attendancewebhook/tempusers_enabled',
                new lang_string('tempusers_enabled_name', 'local_attendancewebhook'),
                new lang_string('tempusers_enabled_description', 'local_attendancewebhook'),
                0
            )
        );

    } else {
        $settings->add(
            new admin_setting_description(
                'local_attendancewebhook/modattendance_is_not_available',
                new lang_string('modattendance_notavailable', 'local_attendancewebhook'),
                new lang_string('modattendance_notavailable_description', 'local_attendancewebhook')
            )
        );
    }
    // Hybridteaching section heading.
    $settings->add(
        new admin_setting_heading(
            'local_attendancewebhook/modhybridteaching_heading',
            new lang_string('modhybridteaching_heading', 'local_attendancewebhook'),
            new lang_string('modhybridteaching_description', 'local_attendancewebhook')
        )
    );
    // Settings for mod_hybridteaching integration.
    if ($hybplugin_available) {
        $settings->add(
            new admin_setting_configcheckbox(
                'local_attendancewebhook/modhybridteaching_enabled',
                new lang_string('modhybridteaching_enabled_name', 'local_attendancewebhook'),
                new lang_string('modhybridteaching_enabled_description', 'local_attendancewebhook'),
                $attplugin_available
            )
        );
        
    } else {
        // Settings description with the message that the plugin mod_hybridteaching is not available.
        $settings->add(
            new admin_setting_description(
                'local_attendancewebhook/modhybridteaching_is_not_available',
                new lang_string('modhybridteaching_notavailable', 'local_attendancewebhook'),
                new lang_string('modhybridteaching_notavailable_description', 'local_attendancewebhook')
            )
        );
    }
    // Heading for field mapping.
    $settings->add(
        new admin_setting_heading(
            'local_attendancewebhook/field_mapping_heading',
            new lang_string('field_mapping_heading', 'local_attendancewebhook'),
            new lang_string('field_mapping_description', 'local_attendancewebhook')
        )
    );
    // Field for matching userid.
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
    $fields = get_user_fieldnames();
    require_once ($CFG->dirroot . '/user/profile/lib.php');
    $customfields = profile_get_custom_fields();
    $userfields = [];
    // Make the keys string values and not indexes.
    foreach ($fields as $field) {
        $userfields[$field] = $field;
        $basicfields[$field] = $field;
    }
    foreach ($customfields as $field) {
        $userfields[$field->shortname] = $field->name;
    }
    // Field for NIA.
    $settings->add(
        new admin_setting_configselect(
            'local_attendancewebhook/field_NIA',
            get_string('restservices_fieldNIA', 'local_attendancewebhook'),
            get_string('restservices_fieldNIA_description', 'local_attendancewebhook'),
            'idnumber',
            $userfields
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
    // Integration REST services.
    $settings->add(
        new admin_setting_heading(
            'local_attendancewebhook/restservices_heading',
            new lang_string('restservices_heading', 'local_attendancewebhook'),
            new lang_string('restservices_description', 'local_attendancewebhook')
        )
    );
    // Enable REST services.
    $settings->add(
        new admin_setting_configcheckbox(
            'local_attendancewebhook/restservices_enabled',
            new lang_string('restservices_enabled_name', 'local_attendancewebhook'),
            new lang_string('restservices_enabled_description', 'local_attendancewebhook'),
            1
        )
    );
    // Generate random API Key.
    $defaultapikey = bin2hex(random_bytes(32));
    // If REST services is enabled, show the following settings.
    // Apikey.
    $settings->add(
        new admin_setting_configtext(
            'local_attendancewebhook/apikey',
            new lang_string('restservices_apikey_name', 'local_attendancewebhook'),
            new lang_string('restservices_apikey_description', 'local_attendancewebhook'),
            $defaultapikey,
            PARAM_TEXT,
            64
        )
    );
    $settings->hide_if('local_attendancewebhook/apikey', 'local_attendancewebhook/restservices_enabled', 'notchecked');
    // API user.
    $settings->add(
        new admin_setting_configtext(
            'local_attendancewebhook/apiuser',
            new lang_string('restservices_apiuser_name', 'local_attendancewebhook'),
            new lang_string('restservices_apiuser_description', 'local_attendancewebhook'),
            'attendance_client',
            PARAM_TEXT,
            64
        )
    );
    $settings->hide_if('local_attendancewebhook/apiuser', 'local_attendancewebhook/restservices_enabled', 'notchecked');


}
