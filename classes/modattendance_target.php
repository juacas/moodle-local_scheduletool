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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/attendance/locallib.php');
/**
 * Class to implement entities that can be targets of attendance marking.
 */
class modattendance_target extends target_base
{
    public \mod_attendance_structure $att_struct;
    public function __construct(event $event, $config)
    {
        parent::__construct($event, $config);
        $this->load_modattendance();
    }
    protected function load_modattendance()
    {
        global $DB;
        if ($this->cm) {
            $attendance = $DB->get_record('attendance', array('id' => $this->cm->instance), '*', MUST_EXIST);
            $this->att_struct = new \mod_attendance_structure($attendance, $this->cm, $this->course, $this->context);
        }
    }
    public function get_session()
    {
        $this->att_struct->pageparams = (object)["sessionid" => $this->sessionid]; // Patch attendance structure.
        return $this->att_struct->get_session_info($this->sessionid);
    }
    /**
     * Check configuration and fix statuses.
     */
    public function check_configuration()
    {
        // Get statuses configured in the attendance activity.
        $statuses = $this->att_struct->get_statuses();
        // Check if lib::STATUS_DESCRIPTIONS and STATUS_ACRONYMS are configured.
        foreach (lib::STATUS_ACRONYMS as $sourcestatus => $acronym) {
            foreach ($statuses as $status) {
                if ($status->acronym == $acronym && $status->description == lib::STATUS_DESCRIPTIONS[$sourcestatus]) {
                    continue 2;
                }
            }
            // If the status is not found, add it.
             // Default value uses setnumber/attendanceid = 0.
            $newstatus = new \stdClass();
            $newstatus->attendanceid = $this->att_struct->id;
            $newstatus->acronym = $acronym;
            $newstatus->description = lib::STATUS_DESCRIPTIONS[$sourcestatus];
            $newstatus->grade = 1;
            $newstatus->setnumber = 0;
            attendance_add_status($newstatus);
        }
    }
    /**
     * Register a single attendance.
     * @param \local_attendancewebhook\attendance $attendance
     * @return void
     */
    public function register_attendance(attendance $attendance)
    {
        global $USER;
        $user = \local_attendancewebhook\lib::get_user_enrol($this->config, $attendance, $this->course);
        if (!$user) {
            if (!\local_attendancewebhook\lib::is_tempusers_enabled($this->config)) {
                $msg = 'User unknown not marked: '.$attendance->get_member();
                lib::log_error($msg);
                $this->errors[] = $msg;
                return;
            } else {
                $tempuser = \local_attendancewebhook\lib::get_tempuser($attendance, $this->course);
                if (!$tempuser) {
                    $this->errors[] = $attendance;
                    return;
                }
            }
        }
        // Get the status from the list of possible statuses.
        $status = \local_attendancewebhook\lib::get_status($this->cm, $attendance);
        if (!$status) {
            $this->errors[] = $attendance;
            return;
        }
        $session = $this->get_session();
        // Logs the attendance.
        $sesslog = new \stdClass();
        $sesslog->sessionid = $session->id;
        $sesslog->studentid = $user ? $user->id : $tempuser->studentid;
        $sesslog->statusid = $status->id;
        $sesslog->timetaken = $attendance->get_server_time();
        $sesslog->remarks = $attendance->get_attendance_note();
        // Sets the author of the log: mod_attendance uses $USER.
        $currentuser = $USER;
        $USER = $this->logtaker_user;

        $this->att_struct->save_log([$sesslog]);
        $USER = $currentuser;
    }
}
