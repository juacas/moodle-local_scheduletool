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

namespace local_attendancewebhook;
use \Exception;
use \mod_attendance_structure;

defined('MOODLE_INTERNAL') || die();
/**
 * Course target refers only a course.
 * It creates a new attendance activity and registers new sessions.
 */
class course_target extends modattendance_target
{
    public \mod_attendance_structure $att_struct;
    public function __construct(object $event, $config)
    {
        global $DB;
        parent::__construct($event, $config);
    }
    /**
     * Populate the class with the course and prefix.
     * @param string $topicId
     * @return void
     */
    function setup_from_topic_id(string $topicId)
    {
        global $DB;
        list($type, $prefix, $courseid) = self::parse_topic_id($topicId);
        $this->prefix = $prefix;
        $course = $DB->get_record('course', ['id' => $courseid], '*');
        if (!$course) {
            throw new \Exception("Invalid Topic {$topicId}:Course not found: {$courseid}");
        } else {
            $this->course = $course;
        }
    }
    /**
     * Parse topicId to get cmid and courseid.
     * @param string $topicId
     * @return array [type, prefix, courseid, sequence]
     */
    public static function parse_topic_id(string $topicId): array
    {
        $topicparts = explode('-', $topicId);
        if (count($topicparts) < 3 || count($topicparts) > 4 || $topicparts[0] == '' || $topicparts[1] != 'course' || !is_numeric($topicparts[2])) {
            throw new \Exception("Invalid course topicId format: {$topicId}");
        }
        return ['course', $topicparts[0], $topicparts[2], $topicparts[3] ?? null];
    }
    /**
     * Search a session with the same opening time. If not found, create a new one.
     * If there are more than one session with the same opening time, log an error.
     * @return mixed session structure or false if not found.
     */
    public function get_session()
    {
        // Check configuration and fix statuses.
        $this->check_configuration();
        // Find a session with the same opening time.
        global $DB;
        $params = ['attendanceid' => $this->cm->instance, 'sessdate' => $this->event->get_opening_time()];
        $sessions = $DB->get_records('attendance_sessions', $params);
        $message = count($sessions) . ' attendance session(s) ' . json_encode($sessions) . ' found.';
        if (count($sessions) > 1) {
            $this->errors[] = $message;
            lib::log_error($message);
            return false;
        } else {
            lib::log_info($message);
            if (count($sessions) == 1) {
                $session = reset($sessions);
                $this->sessionid = $session->id;
                lib::log_info("Attendance session {$session->id} selected for update.");
            } else {
                $session = new \stdClass();
                $session->sessdate = $this->event->get_opening_time();
                $session->duration = $this->event->get_closing_time() == null ? 3600 : $this->event->get_closing_time() - $this->event->get_opening_time();
                $session->groupid = 0;
                $session->description = $this->event->get_event_note();
                $session->descriptionitemid = -1;
                $session->descriptionformat = FORMAT_PLAIN;
                $session->calendarevent = 0; // Disable calendar event creation.
                $session->timemodified = time();
                $session->statusset = 0;
                $session->absenteereport = 1;

                $session->studentscanmark = 1; // Allow students to mark their own attendance.
                $session->rotateqrcode = 1; // Use a rotating QR code.
                $session->rotateqrcodesecret = attendance_random_string();
                $session->studentpassword = attendance_random_string();

                $sessid = $this->att_struct->add_session($session);
                $this->sessionid = $sessid;

                lib::log_info('Attendance session created: ' . json_encode($session));
            }
        }
        return parent::get_session();
    }
    /**
     * Check configuration and fix statuses, names and section position.
     * Creates a new attendance activity if not found.
     */
    public function check_configuration()
    {
        // Checks if the attendance activity is instanced in course.
        // Search attendance module in course with idnumber = self::CM_IDNUMBER using cached cm_modinfo.
        $cminfo = get_fast_modinfo($this->course);
        $cms = $cminfo->get_instances_of('attendance');
        // Filter out the instance in deletion.
        $cms = array_filter($cms, function ($cm) {
            return $cm->idnumber === lib::CM_IDNUMBER && $cm->deletioninprogress === 0;
        });
        $creating_new = count($cms) == 0;
        if ($creating_new) {
            // Create a new attendance activity.
            $moduleinfo = new \stdClass();
            $moduleinfo->course = $this->course->id;
            $moduleinfo->modulename = 'attendance';
            $moduleinfo->section = $this->config->module_section;
            $moduleinfo->visible = 1;
            $moduleinfo->introeditor = ['itemid' => -1, 'idnumber' => 0, 'text' => '', 'format' => FORMAT_PLAIN];
            $moduleinfo->cmidnumber = lib::CM_IDNUMBER;
            $moduleinfo->name = $this->config->module_name;
            $cmodule = create_module($moduleinfo);
            // Get cm_info.
            $cminfo = get_fast_modinfo($this->course);
            $cm = $cminfo->get_instances_of('attendance')[$cmodule->id];
            lib::log_info("New attendance activity created in course {$this->course->shortname} with: " . json_encode($cmodule));
            // Notify the module creation.
            lib::notify(
                $this->config,
                $this->event,
                $this->course->id,
                \core\output\notification::NOTIFY_INFO,
                get_string(
                    "notifications_new_activity",
                    "local_attendancewebhook",
                    [
                        'activityname' => $this->config->module_name,
                        'coursename' => $this->course->shortname,
                        'courseid' => $this->course->id,
                        'cmid' => $cm->id,
                        'activityurl' => new \moodle_url('/mod/attendance/view.php', ['id' => $cm->id]),
                        'courseurl' => new \moodle_url('/course/view.php', ['id' => $this->course->id])
                    ]
                )
            );

        } else {
            $cm = reset($cms);
        }
        $this->set_cm($cm);

        // Initialice the class info.
        $this->load_modattendance();

        // Remove dafault statuses silently. Attendance API trigger events.
        if ($creating_new) {
            global $DB;
            $DB->delete_records('attendance_statuses', ['attendanceid' => $this->cm->instance]);
        }
        // Ensure desired statuses are configured in the attendance activity.
        parent::check_configuration();

        // Check name and section restrictions. Why?
        // Force the name. Why?
        if ($this->att_struct->name != $this->config->module_name) { // TODO: Why name is restored?
            lib::log_info('Module name modified.');
            set_coursemodule_name($this->cm->id, $this->config->module_name);
            lib::log_info('Module name updated.');
        }
        // Force to be in section $config->module_section.        
        if ($this->config->module_section != $cm->sectionnum) {
            lib::log_info('Section number modified.');
            $forcedsection = $cminfo->get_section_info($this->config->module_section);
            if (!$forcedsection) {
                lib::log_info('Section number not found.');
            } else {
                moveto_module($cm, $forcedsection);
                lib::log_info('Section number updated.');
            }
        }
    }
    /**
     * Implements get_topics for course_target.
     */
    static public function get_topics($user): array
    {
        // Collect all courses in which the user is teacher: Has any of:
        // 'mod/attendance:addinstance'
        $topics = [];

        $courses = get_user_capability_course('mod/attendance:addinstance', $user->id);
        if (empty($courses)) {
            return $topics;
        }
        $prefix = get_config('local_attendancewebhook', 'restservices_prefix');

        foreach ($courses as $course) {
            // Get course data.
            $course = lib::get_course_if_allowed($course->id);
            if ($course) {
                // Check start and end dates.
                $now = time();
                if (
                    ($course->startdate && ((int) $course->startdate) > $now) ||
                    ($course->enddate && ((int) $course->enddate) < $now)
                ) {
                    continue;
                }
                // Course can have more than one timetable. Add a sequence number to the topicId.
                $sequence = 0;
                $calendars = self::get_course_calendars($course, $user);
               
                foreach ($calendars as $calendar) {
                    $sequence = hash('md5', json_encode($calendar));
                    if (isset($calendar->timetables)) {
                        $info = get_string('withschedule', 'local_attendancewebhook');
                    } else {
                        $info = get_string('withoutschedule', 'local_attendancewebhook');
                    }
                    $topics[] = (object) [
                        'topicId' => $prefix . '-course-' . $course->id . '-' . $sequence,
                        'name' => $course->shortname,
                        // Only 100 characters.
                        'info' => $info, //substr($course->fullname, 0, 100), // Max 100 chars.
                        'externalIntegration' => true,
                        'tag' => 'course',
                        'calendar' => $calendar,
                    ];
                }
            }
        }
        return $topics;
    }
    /**
     * Get course calendar.
     * @param object $course
     * @param object $user
     * @return array calendars structure.
     */
    static public function get_course_calendars($course, $user = null): array
    {
        $cache_ttl = get_config('local_attendancewebhook', 'local_caches_ttl');
        // Use cache.
        $cache = \cache::make('local_attendancewebhook', 'course_calendar');
        $cachekey = 'course_calendar_' . $course->id;
        $calendar_cache = $cache_ttl > 0 ? $cache->get($cachekey) : false;
        $calendars = [];

        if ($cache_ttl > 0 && $calendar_cache && $calendar_cache->time > (time() - $cache_ttl)) {
            return $calendar_cache->data;
        } else {
            // Get this monday.
            $this_monday = strtotime('monday this week');
            $toDate = strtotime('+28 days', $this_monday);
           
            $results = self::get_schedule_for_course($course, $this_monday, $toDate);
            $calendars = self::parse_course_calendars_pod($course, $results);
            lib::log_info(message: "Got calendar for course $course->id: $course->idnumber " . json_encode($calendars));
            // Default calendar entry.
            if (count($calendars) == 0) {
                // Calculate date ranges with same timetable.
                $calendars = [
                    (object) [
                        'startDate' => $course->startdate ? date('Y-m-d', $course->startdate) : date('Y-m-d'),// format: 2021-09-01
                        'endDate' => $course->enddate ? date('Y-m-d', $course->enddate) : null, // format: 2021-09-01
                    ]
                ];

            }


            // Store in cache.
            if ($cache_ttl > 0) {
                $cache->set($cachekey, (object) ["data" => $calendars, "time" => time()]);
            }
        }
        return $calendars;
    }
    static $scheduleforuser = [];
    /**
     * Get schedule for course.
     * Get schedules from POD view.
     * @param object $course
     * @return array string jsons.
     */
    static public function get_schedule_for_course($course, int $fromDate, int $toDate): array
    {
        $results = [];
        $schedulerestservice = get_config('local_attendancewebhook', 'restservices_schedules_url');
        $examrestservice = get_config('local_attendancewebhook', 'restservices_exams_url');
        $apiKey = get_config('local_attendancewebhook', 'restservices_schedules_apikey');
        
        if ($schedulerestservice != '' || $examrestservice != '') {
            $codsigmas = [];
            // Get redirected courses from course.
            $expandedcourses = lib::get_schedule_equivalent_courses($course);
            lib::log_info("Getting schedule for course $course->id with redirected courses: " . json_encode($expandedcourses));
            // TODO: get all idnumbers.
            foreach ($expandedcourses as $selectedcourse) {
                // Get ids for rest service.
                $codsigmas[] = self::parse_id_from_course($selectedcourse);
            }
    
            lib::log_info("Getting schedule for course $course->id with codsigmas: " . json_encode($codsigmas));
            foreach ($codsigmas as $codsigma) {
                if ($codsigma == '') {
                    continue;
                }
                 // Format m/d/Y.
                $fromDate = date('m/d/Y', (int) $fromDate);
                $toDate = date('m/d/Y', (int) $toDate);
                // Make POST formencoded request with curl.
                //$codsigma = 46671; // TODO debug
                $postfields = [
                    'apikey' => $apiKey,
                    "id" => $codsigma,
                    "dateFrom" => $fromDate,
                    "dateTo" => $toDate
                ];
                // Encode postfields.
                $postfields = http_build_query($postfields, '', '&');
                if ($schedulerestservice != ''){
                    $curl = curl_init($schedulerestservice);
                    curl_setopt_array($curl, array(
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 1,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $postfields,
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/x-www-form-urlencoded'
                        ),
                    ));
                    $result =  curl_exec($curl);
                    $results[] = $result;
        
                    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    $error = curl_error($curl);
                    // $info = curl_getinfo($curl);
                    lib::log_info("Got schedule from $schedulerestservice for cod $codsigma from $fromDate to $toDate --> status $status($error): $result");
                }
                if ($examrestservice != ''){
                    $curl = curl_init($examrestservice);
                    curl_setopt_array($curl, array(
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 1,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $postfields,
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/x-www-form-urlencoded'
                        ),
                    ));
                    $result =  curl_exec($curl);
                    $results[] = $result;
        
                    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    $error = curl_error($curl);
                    // $info = curl_getinfo($curl);
                    lib::log_info("Got exams from $examrestservice for cod $codsigma from $fromDate to $toDate --> status $status($error): $result");
                }
                curl_close($curl);
            } 
        }
        return $results;
    }
    /**
     * Parse course id from course object according to settings.
     */
    static public function parse_id_from_course($course): string {
        // Parse idnumber eith regexpr.
        $courseidregex = get_config('local_attendancewebhook', 'restservices_courseid_regex');
        if ($courseidregex == '') {
            return '';
        }
        if (!preg_match($courseidregex, $course->idnumber, $matches)) {
            return '';
        }
        return $matches[1];
    }
    /**
     * Get schedule for user.
     * TODO: Get schedules from POD.
     */
    static public function get_schedule_for_user($user): string
    {
        $restservice = get_config('local_attendancewebhook', 'restservices_schedules_url');
        if ($restservice == '') {
            return '';
        }
        if (isset(self::$scheduleforuser[$user->id])) {
            return self::$scheduleforuser[$user->id];
        }
        $apiKey = get_config('local_attendancewebhook', 'restservices_schedules_apikey');
        $dni = strtoupper(substr($user->username, 1)); // TODO get real DNI.
        // Get this monday.
        $monday = strtotime('monday this week');
        // Format m/d/Y.
        $day = date('m/d/Y', $monday);

        $body = json_encode([
            'apiKey' => $apiKey,
            "lan" => "es",
            "document" => $dni,
            "role" => "PROFESOR",
            "year" => $day
        ]);
        // Make request with curl.
        $ch = curl_init($restservice);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = [
            'Content-Type: application/json',
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        lib::log_info("Got schedule from $restservice with $body --> status $status($error): $result");
        curl_close($ch);
        self::$scheduleforuser[$user->id] = $result;
        return $result;
    }
    /**
     * Parse Json and get course calendar summarizing by course names and dates.
     * Slots came from a REST service.
     * Course name is followed by a coding in parenthesis that are to be ignored.
     * @param object $course
     * @param array  string $jsons
     * @return array sessions structure.
     */
    static public function parse_course_calendars_pod($course, array $jsons): array
    {
        $calendars = [];
        // Fuse json entries.
        $data = [];
        foreach ($jsons as $jsonentry) {
            $entry = json_decode($jsonentry);
            $data = array_merge($data, $entry??[]);
        }
      
        // Iterate grouping in a week.
        $weekdays = ["L", "M", "X", "J", "V", "S", "D"];
        $calendars_by_week = [];
        foreach ($data as $slot) {
            // Complete fechainicio and fechafin if only has fecha (in exams structure).
            // "fecha" format is d/m/Y convert to Y-m-d.
            $fecha = date('Y-m-d', strtotime(str_replace('/', '-', $slot->fecha)));
            if (!isset($slot->fechaInicio)) {
                $slot->fechaInicio = $fecha;
            }
            if (!isset($slot->fechaFin)) {
                $slot->fechaFin = $fecha;
            }
            $weeknumber = date('W', strtotime($slot->fechaInicio));
            $weekday = date('N', strtotime($slot->fechaInicio));
            $timetable = (object)[
                'weekdays' => $weekdays[$weekday - 1],
                'startTime' => $slot->horaInicio,
                'endTime' => $slot->horaFin,
                "info" => "$slot->nombreGrupo ($slot->nombreUbicacion)",
            ];
            if ($calentry = $calendars_by_week[$weeknumber] ?? false) {
                // Add timetable to existent calentry if not exists.
                if (!in_array($timetable, $calentry->timetables)) {
                    $calentry->timetables[] = $timetable;
                }
            } else {
                // Get "monday this week" for $slot->fechaInicio.
                $startweek = strtotime('monday this week', strtotime($slot->fechaInicio));
                $startweekdate = date('Y-m-d', $startweek);
                $endweekdate = date('Y-m-d', strtotime($startweekdate . ' + 6 days'));
                // Create new entry.
                $calentry = (object) [
                    'startDate' => $startweekdate,
                    'endDate' => $endweekdate,
                    'timetables' => [$timetable],
                ];
                $calendars_by_week[$weeknumber] = $calentry;
            }
            ;
        }
        $calendars = lib::compress_calendars($calendars_by_week);
        return $calendars;
    }
    /**
     * Parse Json and get course calendar summarizing by course names and dates.
     * Slots came from a REST service.
     * Course name is followed by a coding in parenthesis that are to be ignored.
     * @param object $course
     * @param string $json
     * @return array sessions structure.
     */
    static public function parse_course_calendars_appcrue($course, $json): array
    {
        $calendars = [];
        $data = json_decode($json);
        // Course name is followed by a coding in parenthesis that are to be ignored.
        $coursename = trim(explode('(', $course->fullname)[0]);

        if ($data) {
            foreach ($data as $slot) {
                // Parse date in formar YYYYDDMM.
                $date = substr($slot->day, 0, 4) . '-' . substr($slot->day, 4, 2) . '-' . substr($slot->day, 6, 2);
                foreach ($slot->schedule as $session) {
                    // Check coursename coincidence. Compare strings uppercase, no accents.
                    if (strtoupper(self::strip_accents($session->subjectname)) != strtoupper(self::strip_accents($coursename))) {
                        continue;
                    }
                    // Limit to 14 days.
                    if (strtotime($date) > strtotime('+14 days')) {
                        continue;
                    }
                    $session->sessdate = strtotime($date . ' ' . $session->starts);
                    $session->duration = strtotime($date . ' ' . $session->ends) - $session->sessdate;
                    $session->description = "$session->groupname $session->location";
                    $calendars[] = modattendance_target::get_single_day_calendar($session, $session->description);
                }
            }
        }
        return $calendars;
    }
    /**
     * Strip accents.
     */
    static public function strip_accents($string): string
    {
        return strtr(utf8_decode($string), utf8_decode('áéíóúÁÉÍÓÚüÜ'), 'aeiouAEIOUuU');
    }
}
