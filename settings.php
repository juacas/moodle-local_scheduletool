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

use local_scheduletool\admin_settings_coursecat_multiselect;
use local_scheduletool\admin_setting_configmultiselect_autocomplete;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/scheduletool/settingslib.php');

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_scheduletool', new lang_string('pluginname', 'local_scheduletool'));

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
            'local_scheduletool/modattendance_heading',
            new lang_string('modattendance_heading', 'local_scheduletool'),
            new lang_string('modattendance_description', 'local_scheduletool')
        )
    );
    // Settings for mod_attendance integration.
    if ($attplugin_available) {
        $settings->add(
            new admin_setting_configcheckbox(
                'local_scheduletool/modattendance_enabled',
                new lang_string('modattendance_enabled_name', 'local_scheduletool'),
                new lang_string('modattendance_enabled_description', 'local_scheduletool'),
                $attplugin_available
            )
        );
        
        // Checkbox for exporting mod_attendance sessions as topics.
        $settings->add(
            new admin_setting_configcheckbox(
                'local_scheduletool/export_sessions_as_topics',
                new lang_string('export_sessions_as_topics_name', 'local_scheduletool'),
                new lang_string('export_sessions_as_topics_description', 'local_scheduletool'),
                1
            )
        );
        $settings->hide_if('local_scheduletool/export_sessions_as_topics', 'local_scheduletool/modattendance_enabled', 'notchecked');
        // Checkbox for exporting courses as topics.
        $settings->add(
            new admin_setting_configcheckbox(
                'local_scheduletool/export_courses_as_topics',
                new lang_string('export_courses_as_topics_name', 'local_scheduletool'),
                new lang_string('export_courses_as_topics_description', 'local_scheduletool'),
                1
            )
        );
        $settings->hide_if('local_scheduletool/export_courses_as_topics', 'local_scheduletool/modattendance_enabled', 'notchecked');
        // Skip redirected courses: checkbox.
        $settings->add(
            new admin_setting_configcheckbox(
                'local_scheduletool/skip_redirected_courses',
                new lang_string('skip_redirected_courses_name', 'local_scheduletool'),
                new lang_string('skip_redirected_courses_description', 'local_scheduletool'),
                1
            )
        );
        $settings->hide_if('local_scheduletool/skip_redirected_courses', 'local_scheduletool/export_courses_as_topics', 'notchecked');
        // Setting for creating a new instance for course topics.
        $settings->add(
            new admin_setting_configtext(
                'local_scheduletool/module_name',
                new lang_string('module_name_name', 'local_scheduletool'),
                new lang_string('module_name_description', 'local_scheduletool'),
                new lang_string('pluginname', 'local_scheduletool'),
                PARAM_TEXT,
                64
            )
        );
        $settings->hide_if('local_scheduletool/module_name', 'local_scheduletool/modattendance_enabled', 'notchecked');
        $settings->hide_if('local_scheduletool/module_name', 'local_scheduletool/export_courses_as_topics', 'notchecked');
        
        // User for creating instances with rest services.
        // Select an user to execute services on behalf of.
        // $settings->add(
        //     new admin_setting_users_with_capability(
        //         'local_scheduletool/creatoruser',
        //         new lang_string('restservices_useronbehalf', 'local_scheduletool'),
        //         new lang_string('restservices_useronbehalf_description', 'local_scheduletool'),
        //         [],
        //         'mod/attendance:addinstance'
        //     )
        // );
        // $settings->hide_if('local_scheduletool/creatoruser', 'local_scheduletool/export_courses_as_topics', 'notchecked');
        // $settings->hide_if('local_scheduletool/creatoruser', 'local_scheduletool/modattendance_enabled', 'notchecked');
        // $settings->hide_if('local_scheduletool/creatoruser', 'local_scheduletool/restservices_enabled', 'notchecked');
       

        $settings->add(
            new admin_setting_configtext(
                'local_scheduletool/module_section',
                new lang_string('module_section_name', 'local_scheduletool'),
                new lang_string('module_section_description', 'local_scheduletool'),
                0,
                PARAM_INT,
                64
            )
        );
        $settings->hide_if('local_scheduletool/module_section', 'local_scheduletool/modattendance_enabled', 'notchecked');
        $settings->hide_if('local_scheduletool/module_section', 'local_scheduletool/export_courses_as_topics', 'notchecked');

        // $settings->add(
        //     new admin_setting_configselect(
        //         'local_scheduletool/course_id',
        //         new lang_string('course_id_name', 'local_scheduletool'),
        //         new lang_string('course_id_description', 'local_scheduletool'),
        //         'shortname',
        //         array('shortname' => 'shortname', 'idnumber' => 'idnumber')
        //     )
        // );
        // $settings->hide_if('local_scheduletool/course_id', 'local_scheduletool/modattendance_enabled', 'notchecked');
        // $settings->hide_if('local_scheduletool/course_id', 'local_scheduletool/export_courses_as_topics', 'notchecked');
        // Temporary users.
        $settings->add(
            new admin_setting_configcheckbox(
                'local_scheduletool/tempusers_enabled',
                new lang_string('tempusers_enabled_name', 'local_scheduletool'),
                new lang_string('tempusers_enabled_description', 'local_scheduletool'),
                0
            )
        );

    } else {
        $settings->add(
            new admin_setting_description(
                'local_scheduletool/modattendance_is_not_available',
                new lang_string('modattendance_notavailable', 'local_scheduletool'),
                new lang_string('modattendance_notavailable_description', 'local_scheduletool')
            )
        );
    }
    /*************************************
     * Hybridteaching section heading.
     ************************************/
    $settings->add(
        new admin_setting_heading(
            'local_scheduletool/modhybridteaching_heading',
            new lang_string('modhybridteaching_heading', 'local_scheduletool'),
            new lang_string('modhybridteaching_description', 'local_scheduletool')
        )
    );
    // Settings for mod_hybridteaching integration.
    if ($hybplugin_available) {
        $settings->add(
            new admin_setting_configcheckbox(
                'local_scheduletool/modhybridteaching_enabled',
                new lang_string('modhybridteaching_enabled_name', 'local_scheduletool'),
                new lang_string('modhybridteaching_enabled_description', 'local_scheduletool'),
                $attplugin_available
            )
        );

    } else {
        // Settings description with the message that the plugin mod_hybridteaching is not available.
        $settings->add(
            new admin_setting_description(
                'local_scheduletool/modhybridteaching_is_not_available',
                new lang_string('modhybridteaching_notavailable', 'local_scheduletool'),
                new lang_string('modhybridteaching_notavailable_description', 'local_scheduletool')
            )
        );
    }
    /***********************************
     * Heading for field mapping.
     **********************************/
    $settings->add(
        new admin_setting_heading(
            'local_scheduletool/field_mapping_heading',
            new lang_string('field_mapping_heading', 'local_scheduletool'),
            new lang_string('field_mapping_description', 'local_scheduletool')
        )
    );
    // Field for matching userid.
    $settings->add(
        new admin_setting_configselect(
            'local_scheduletool/user_id',
            new lang_string('user_id_name', 'local_scheduletool'),
            new lang_string('user_id_description', 'local_scheduletool'),
            'username',
            array('username' => 'username', 'email' => 'email')
        )
    );

    $settings->add(
        new admin_setting_configselect(
            'local_scheduletool/member_id',
            new lang_string('member_id_name', 'local_scheduletool'),
            new lang_string('member_id_description', 'local_scheduletool'),
            'username',
            array('username' => 'username', 'email' => 'email')
        )
    );
    $fields = get_user_fieldnames();
    require_once($CFG->dirroot . '/user/profile/lib.php');
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
            'local_scheduletool/field_NIA',
            get_string('restservices_fieldNIA', 'local_scheduletool'),
            get_string('restservices_fieldNIA_description', 'local_scheduletool'),
            'idnumber',
            $userfields
        )
    );
    // Checkbox for enabling user list in topics.
    $settings->add(
        new admin_setting_configcheckbox(
            'local_scheduletool/userlist_enabled',
            new lang_string('userlist_enabled_name', 'local_scheduletool'),
            new lang_string('userlist_enabled_description', 'local_scheduletool'),
            0
        )
    );
    // Checkbox for enabling autoenrolment functionality.
    $settings->add(
        new admin_setting_configcheckbox(
            'local_scheduletool/autoenrol_enabled',
            new lang_string('autoenrol_enabled_name', 'local_scheduletool'),
            new lang_string('autoenrol_enabled_description', 'local_scheduletool'),
            0
        )
    );
    // Notifications.
    $settings->add(
        new admin_setting_configcheckbox(
            'local_scheduletool/notifications_enabled',
            new lang_string('notifications_enabled_name', 'local_scheduletool'),
            new lang_string('notifications_enabled_description', 'local_scheduletool'),
            0
        )
    );
    // Enable calendar compactation.
    $settings->add(
            setting: new admin_setting_configcheckbox(
            name: 'local_scheduletool/compact_calendar',
            visiblename: new lang_string('compact_calendar_name', 'local_scheduletool'),
            description: new lang_string('compact_calendar_description', 'local_scheduletool'),
            defaultsetting: 0
        )
    );
    // Enable menu action for copying sessions from schedules.
    $settings->add(
        new admin_setting_configcheckbox(
            'local_scheduletool/copy_schedule_enabled',
            new lang_string('copy_schedule', 'local_scheduletool'),
            new lang_string('copy_schedule_description', 'local_scheduletool'),
            0
        )
    );
    /********************************
     * Integration REST services.
     ********************************/
    $settings->add(
        new admin_setting_heading(
            'local_scheduletool/restservices_heading',
            new lang_string('restservices_heading', 'local_scheduletool'),
            new lang_string('restservices_description', 'local_scheduletool')
        )
    );
    // Enable REST services
    // TODO: honor get_config('core', 'webserviceprotocols') rest config.
    $settings->add(
        new admin_setting_configcheckbox(
            'local_scheduletool/restservices_enabled',
            new lang_string('restservices_enabled_name', 'local_scheduletool'),
            new lang_string('restservices_enabled_description', 'local_scheduletool'),
            1
        )
    );
    
     // Enable only in these course categories.
     $settings->add(
        new admin_settings_coursecat_multiselect(
            'local_scheduletool/enableincategories',
            get_string('restservices_enableincategories', 'local_scheduletool'),
            get_string('restservices_enableincategories_description', 'local_scheduletool'))
    );
    $settings->hide_if('local_scheduletool/enableincategories', 'local_scheduletool/restservices_enabled', 'notchecked');
    // Disabled in these course categories.
    $settings->add(
        new admin_settings_coursecat_multiselect(
            'local_scheduletool/disableincategories',
            get_string('restservices_disableincategories', 'local_scheduletool'),
            get_string('restservices_disableincategories_description', 'local_scheduletool'))
    );
    // Generate random API Key.
    $exampleapikey = bin2hex(random_bytes(16));
    // If REST services is enabled, show the following settings.
    // Apikey.
    $settings->add(
        new admin_setting_configtext(
            'local_scheduletool/apikey',
            new lang_string('restservices_apikey_name', 'local_scheduletool'),
            new lang_string('restservices_apikey_description', 'local_scheduletool', $exampleapikey),
            !get_config('local_scheduletool/apikey') ? $exampleapikey: null,
            PARAM_TEXT,
            64
        )
    );
    $settings->hide_if('local_scheduletool/apikey', 'local_scheduletool/restservices_enabled', 'notchecked');
    // API user.
    $settings->add(
        new admin_setting_configtext(
            'local_scheduletool/apiuser',
            new lang_string('restservices_apiuser_name', 'local_scheduletool'),
            new lang_string('restservices_apiuser_description', 'local_scheduletool'),
            'attendance_client',
            PARAM_TEXT,
            64
        )
    );
    $settings->hide_if('local_scheduletool/apiuser', 'local_scheduletool/restservices_enabled', 'notchecked');
    // Prefix for identifiers in REST services.
    $settings->add(
        new admin_setting_configtext(
            'local_scheduletool/restservices_prefix',
            new lang_string('restservices_prefix_name', 'local_scheduletool'),
            new lang_string('restservices_prefix_description', 'local_scheduletool'),
            'moodle',
            PARAM_TEXT,
            64
        )
    );
    $settings->hide_if('local_scheduletool/restservices_prefix', 'local_scheduletool/restservices_enabled', 'notchecked');

    // REST Multiplexer.
    // Enter a list of enndpoints line by line for getTopic endpoint.
    $settings->add(
        new admin_setting_configtextarea(
            'local_scheduletool/restservices_getTopics',
            new lang_string('restservices_getTopics_name', 'local_scheduletool'),
            new lang_string('restservices_getTopics_description', 'local_scheduletool'),
            '',
            PARAM_RAW_TRIMMED
        )
    );
    $settings->hide_if('local_scheduletool/restservices_getTopics', 'local_scheduletool/restservices_enabled', 'notchecked');
    // Enter a list of enndpoints line by line for getUserData endpoint.
    $settings->add(
        new admin_setting_configtextarea(
            'local_scheduletool/restservices_getUserData',
            new lang_string('restservices_getUserData_name', 'local_scheduletool'),
            new lang_string('restservices_getUserData_description', 'local_scheduletool'),
            '',
            PARAM_TEXT
        )
    );
    $settings->hide_if('local_scheduletool/restservices_getUserData', 'local_scheduletool/restservices_enabled', 'notchecked');
    // Enter a list of endpoints line by line for closeEvent endpoint.
    $settings->add(
        new admin_setting_configtextarea(
            'local_scheduletool/restservices_closeEvent',
            new lang_string('restservices_closeEvent_name', 'local_scheduletool'),
            new lang_string('restservices_closeEvent_description', 'local_scheduletool'),
            '',
            PARAM_TEXT
        )
    );
    $settings->hide_if('local_scheduletool/restservices_closeEvent', 'local_scheduletool/restservices_enabled', 'notchecked');
    // Enter a list of enndpoints line by line for signUp endpoint.
    $settings->add(
        new admin_setting_configtextarea(
            'local_scheduletool/restservices_signUp',
            new lang_string('restservices_signUp_name', 'local_scheduletool'),
            new lang_string('restservices_signUp_description', 'local_scheduletool'),
            '',
            PARAM_TEXT
        )
    );
    $settings->hide_if('local_scheduletool/restservices_signUp', 'local_scheduletool/restservices_enabled', 'notchecked');

    // URL rest service for schedules.
    $settings->add(
        new admin_setting_configtext(
            'local_scheduletool/restservices_schedules_url',
            new lang_string('restservices_schedules_name', 'local_scheduletool'),
            new lang_string('restservices_schedules_description', 'local_scheduletool'),
            '',
            PARAM_URL,
            256
        )
    );
    // URL rest service for exams.
    $settings->add(
        new admin_setting_configtext(
            'local_scheduletool/restservices_exams_url',
            new lang_string('restservices_exams_name', 'local_scheduletool'),
            new lang_string('restservices_exams_description', 'local_scheduletool'),
            '',
            PARAM_URL,
            256
        )
    );
    // Apikey for schedules.
    $settings->add(
        new admin_setting_configtext(
            'local_scheduletool/restservices_schedules_apikey',
            new lang_string('restservices_schedules_apikey_name', 'local_scheduletool'),
            new lang_string('restservices_schedules_apikey_description', 'local_scheduletool'),
            '',
            PARAM_TEXT,
            64
        )
    );
    // Regular expression for extracting the REST id from course->idnumber.
    $settings->add(
        new admin_setting_configtext(
            'local_scheduletool/restservices_courseid_regex',
            new lang_string('restservices_courseid_regex_name', 'local_scheduletool'),
            new lang_string('restservices_courseid_regex_description', 'local_scheduletool'),
            '/\d+-\d+-\d+-(\d+)-.*/',
            PARAM_TEXT,
            64
        )
    );


    // Link to page getlogs.php.
    $apiparam = get_config('local_scheduletool', 'apikey');
    $logurl = new \moodle_url('/local/scheduletool/getlogs.php', ['apikey' => $apiparam]);

    // Debug list of authorized orgnizers.
    $settings->add(
        new admin_setting_configtextarea(
            'local_scheduletool/authorized_organizers',
            new lang_string('authorized_organizers_name', 'local_scheduletool'),
            new lang_string('authorized_organizers_description', 'local_scheduletool'),
            '',
            PARAM_TEXT
        )
    );
    // White list of authorized users.
    $settings->add(
        new admin_setting_configtextarea(
            'local_scheduletool/authorized_users',
            new lang_string('authorized_users_name', 'local_scheduletool'),
            new lang_string('authorized_users_description', 'local_scheduletool'),
            '',
            PARAM_TEXT
        )
    );
    // Checkbox for enabling the logs.
    $settings->add(
        new admin_setting_configcheckbox(
            'local_scheduletool/logs_enabled',
            new lang_string('logs_enabled_name', 'local_scheduletool'),
            new lang_string('logs_enabled_description', 'local_scheduletool', $logurl->out()),
            0
        )
    );
   
    // Enable caches.
    $settings->add(
        new admin_setting_configtext(
            'local_scheduletool/local_caches_ttl',
            new lang_string('local_caches_ttl', 'local_scheduletool'),
            new lang_string('local_caches_ttl_description', 'local_scheduletool'),
            600,
            PARAM_INT,
            10
        )
    );
     // Enable caches.
     $settings->add(
        new admin_setting_configtext(
            'local_scheduletool/remote_caches_ttl',
            new lang_string('remote_caches_ttl', 'local_scheduletool'),
            new lang_string('remote_caches_ttl_description', 'local_scheduletool'),
            1800,
            PARAM_INT,
            10
        )
    );
}
