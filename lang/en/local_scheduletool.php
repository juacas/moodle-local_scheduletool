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

$string['course_id_description'] = 'Moodle course ID corresponding to attendance activity ID (topic_id)';
$string['course_id_name'] = 'Course ID';
$string['invalid_data'] = 'Invalid data.';
$string['member_id_description'] = 'JSON Attendance member field to use to match moodle user ID field.';
$string['member_id_name'] = 'Member ID';
$string['module_name_description'] = 'Moodle attendance module name';
$string['module_name_name'] = 'Module name';
$string['module_section_description'] = 'Moodle attendance module section number';
$string['module_section_name'] = 'Module section';
$string['notification_messages'] = 'Outcomes:';
$string['notification_contact_admin'] = 'Please contact the administrator.';
$string['notification_error_attendances'] = 'The following attendances of {$a->event}} could not be incorporated:';
$string['notification_error_event'] = 'The registration in {$a->event} could not be incorporated:';
$string['notification_info'] = 'Registration in "{$a->topic}" successfully completed.';
$string['notification_subject'] = 'Attendance control results for {$a->topic}';
$string['notifications_enabled_description'] = 'Enable or disable notifications';
$string['notifications_enabled_name'] = 'Notifications';
$string['notifications_new_activity'] = 'New attendance module "{$a->activityname}" was created in course "{$a->coursename}".';
$string['notifications_user_unknown_notmarked'] = 'User not found, not marked: {$a}';

$string['pluginname'] = 'Schedule tools';
$string['privacy:metadata'] = 'Attendance Webhook plugin stores data on behalf of Attendance plugin.';
$string['messageprovider:error'] = 'Attendance Webhook notifications error events';
$string['tempusers_enabled_description'] = 'Enable or disable the creation of temporary users';
$string['tempusers_enabled_name'] = 'Temporary users';
$string['user_id_description'] = 'Moodle user ID field to use for matching attendance member ID field in JSON';
$string['user_id_name'] = 'User ID';

$string['restservices_heading'] = 'REST services';
$string['restservices_description'] = 'REST services can be used to report user data and list of topics for each user. This plugin is used to integrate with Datio\'s Asistencia';
$string['restservices_enabled_name'] = 'Enable REST services';
$string['restservices_enabled_description'] = 'Enable or disable REST services.';
$string['restservices_apikey_name'] = 'API key';
$string['restservices_apikey_description'] = 'API key for REST services access. Random example: {$a}';
$string['restservices_apiuser_name'] = 'API user';
$string['restservices_apiuser_description'] = 'API user for REST services client';
$string['restservices_fieldNIA'] = 'User field for NIA';
$string['restservices_fieldNIA_description'] = 'User field where NIA is stored';
$string['restservices_getTopics_name'] = 'Get topics';
$string['restservices_getTopics_description'] = 'Multiplexer for getTopics endpoint. Enter an endpoint URL in each line with APIKey and APIClient. Format: Prefix|URL';
$string['restservices_getUserData_name'] = 'Get user data';
$string['restservices_getUserData_description'] = 'Multiplexer for getUserData endpoint. Enter an endpoint URL in each line with APIKey and APIClient.  Format: Prefix|URL';
$string['restservices_closeEvent_name'] = 'Close event';
$string['restservices_closeEvent_description'] = 'Multiplexer for closeEvent endpoint. Enter an endpoint URL in each line with APIKey and APIClient.  Format: Prefix|URL';
$string['restservices_signUp_name'] = 'Sign up endpoint';
$string['restservices_signUp_description'] = 'Multiplexer for signUp endpoint. Enter an endpoint URL in each line with APIKey and APIClient.  Format: Prefix|URL';
$string['restservices_prefix_name'] = 'Prefix';
$string['restservices_prefix_description'] = 'Prefix for the topicIds.';
$string['restservices_useronbehalf'] = 'User on behalf';
$string['restservices_useronbehalf_description'] = 'User with global "mod/attendance:addinstance" permission on behalf of which the request is made. If an event is created from a course a new instance of mod_attendance is created.';
$string['restservices_enableincategories'] = 'Enable only in this categories';
$string['restservices_enableincategories_description'] = 'Enable the plugin only in these course categories (and its subcategories). All categories if empty.';
$string['restservices_disableincategories'] = 'Disable in these categories';
$string['restservices_disableincategories_description'] = 'Disable the plugin in these course categories (and its subcategories).';
$string['restservices_schedules_name'] = 'Schedules Rest service';
$string['restservices_schedules_description'] = 'Schedules Rest service URL';
$string['restservices_exams_name'] = 'Exams URL';
$string['restservices_exams_description'] = 'Exams Rest service URL';
$string['restservices_schedules_apikey_name'] = 'API key';
$string['restservices_schedules_apikey_description'] = 'API key for schedules Rest service access.';
$string['restservices_courseid_regex_name'] = 'Course ID regex';
$string['restservices_courseid_regex_description'] = 'Regular expression to extract from idnumber the course "id" param used in the Rest service URL. Example: /\d+-\d+-\d+-(\d+)-.*/ for idnumber 45634 like "1-22-1-45634-1-2025"';
$string['authorized_organizers_name'] = 'Authorized organizers';
$string['authorized_organizers_description'] = 'List of authorized organizers. If set only these users can be organizers. One email per line. Format: Email';
$string['authorized_users_name'] = 'Authorized users';
$string['authorized_users_description'] = 'List of authorized users. If set only these users can access the plugin. One email per line. Format: Email';
$string['skip_redirected_courses_name'] = 'Skip redirected courses';
$string['skip_redirected_courses_description'] = 'Courses with course_format: redirected (See <a href="https://moodle.org/plugins/format_redirected">https://moodle.org/plugins/format_redirected</a>) will not be processed.';
$string['local_caches_ttl'] = 'TTL local cache';
$string['local_caches_ttl_description'] = 'Enable or disable plugin\'s local caches. Caches are used to store local user_topics and course_calendar data for performance reasons. ttl=0 means no cache.';
$string['remote_caches_ttl'] = 'TTL proxys cache';
$string['remote_caches_ttl_description'] = 'Enable or disable plugin\'s remotes caches. Caches are used to store remotes user_topics data for performance reasons. ttl=0 means no cache.';
$string['modattendance_heading'] = 'mod_attendance integration';
$string['modattendance_description'] = 'mod_attendance plugin can be used to manage attendance in Moodle. This plugin can be integrated with Datio\'s Asistencia';
$string['modattendance_enabled_name'] = 'Enable mod_attendance integration';
$string['modattendance_enabled_description'] = 'Enable or disable mod_attendance integration';
$string['modattendance_notavailable'] = 'mod_attendance plugin not available';
$string['modattendance_notavailable_description'] = 'mod_attendance plugin can be used to manage attendance in Moodle. You can find it at <a href="https://moodle.org/plugins/mod_attendance">https://moodle.org/plugins/mod_attendance</a>';

$string['export_sessions_as_topics_name'] = 'Export sessions as topics';
$string['export_sessions_as_topics_description'] = 'Each near session in any instance of mod_attendance will be exported as a topic';
$string['export_courses_as_topics_name'] = 'Export courses as topics';
$string['export_courses_as_topics_description'] = 'Each course will be exported as a topic storage. A new attendance activity will be created for each course to store attendance sessions';
$string['modhybridteaching_heading'] = 'mod_hybridteaching integration';
$string['modhybridteaching_description'] = 'mod_hybridteaching plugin can be used to manage attendance in Moodle. More information at <a href="https://unimoodle.github.io/moodle-mod_hybridteaching/">https://unimoodle.github.io/moodle-mod_hybridteaching/</a> (Spanish). This plugin can be integrated with Datio\'s Asistencia';
$string['modhybridteaching_enabled_name'] = 'Enable mod_hybridteaching integration';
$string['modhybridteaching_enabled_description'] = 'Enable or disable mod_hybridteaching integration';
$string['modhybridteaching_notavailable'] = 'mod_hybridteaching plugin not available';
$string['modhybridteaching_notavailable_description'] = 'mod_hybridteaching plugin can be used to manage attendance in Moodle. You can find it at <a href="https://unimoodle.github.io/moodle-mod_hybridteaching/">https://unimoodle.github.io/moodle-mod_hybridteaching/</a>';

$string['field_mapping_heading'] = 'Field mapping';
$string['field_mapping_description'] = 'Field names from which to extract user data';
$string['logs_enabled_name'] = 'Enable logs';
$string['logs_enabled_description'] = 'Enable or disable logs. Logs are written to moodle_data/scheduletool/logs/trace.log and can be viewed in <a href="{$a}">GetLogs</a>.';

$string['withoutschedule'] = 'No schedule';
$string['withschedule'] = 'As in schedule';
$string['compact_calendar_name'] = 'Compact calendar';
$string['compact_calendar_description'] = 'Compact calendar representation using repetitive sessions with the same topic id. If disabled, every session is a different topic id .';
$string['copy_schedule'] = 'Copy schedule';
$string['copy_schedule_description'] = 'Copy schedule from oficial schedules to mod_attendance instances.';
$string['copy_shedule_course'] = 'Create a activity named "{$a->attendancename}" in course "{$a->coursename}" and copy official schedules to track attendance';
$string['no_timetables'] = 'No timetables available';
$string['count_sessions_added'] = '{$a} attendance tracking sessions added.';