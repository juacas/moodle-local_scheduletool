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

namespace local_scheduletool;

defined('MOODLE_INTERNAL') || die();

class attendance {

    private $member;

    private $mode;

    private $attendance_note;

    private $server_time;

    public function __construct($object) {
        if (!is_object($object) && ! $object instanceof \stdClass) {
            throw new \moodle_exception('invalid_data', 'local_scheduletool');
        }
        $this->set_member($object->member);
        $this->set_mode(clean_param($object->mode, PARAM_NOTAGS));
        $this->set_attendance_note(clean_param($object->attendanceNote, PARAM_TEXT));
        $this->set_server_time(clean_param($object->serverTime, PARAM_NOTAGS));
    }

    public function __toString() {
        return strval($this->get_member());
    }

    public function get_member() {
        return $this->member;
    }

    public function set_member($member) {
        $this->member = new member($member);
    }

    public function get_mode() {
        return $this->mode;
    }

    public function set_mode($mode) {
        $this->mode = $mode;
    }

    public function get_attendance_note() {
        return $this->attendance_note;
    }

    public function set_attendance_note($attendance_note) {
        $this->attendance_note = $attendance_note;
    }

    public function get_server_time() {
        return $this->server_time;
    }

    public function set_server_time($server_time) {
        if (($timestamp = strtotime($server_time)) === false) {
            throw new \moodle_exception('invalid_data', 'local_scheduletool');
        } else {
            $this->server_time = $timestamp;
        }
    }

}
