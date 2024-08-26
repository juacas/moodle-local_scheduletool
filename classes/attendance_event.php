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

class attendance_event
{
    private $topic;
    private $opening_time;
    private $closing_time;
    private $event_note;
    private $member;
    private $server_time;
    private $attendance_note;
    private $mode;
    private $source;

    public function __construct($json)
    {
        $this->source = $json;
        $object = json_decode($json);
        if (!is_object($object) && ! $object instanceof \stdClass) {
            throw new \moodle_exception('invalid_data', 'local_attendancewebhook');
        }
        $this->set_topic($object->topic);
        $this->set_opening_time(clean_param($object->openingTime, PARAM_NOTAGS));
        $object->closingTime? $this->set_closing_time(clean_param($object->closingTime, PARAM_NOTAGS)) : null;
        $this->set_event_note(clean_param($object->eventNote, PARAM_TEXT));
        $this->set_member($object->member);
        $this->set_server_time($object->serverTime);
        $this->set_mode($object->mode);
        $this->set_attendance_note($object->attendanceNote);
    }

    public function __toString()
    {
        return date('d-m-Y H:i:s', $this->get_opening_time()) . ' - ' . strval($this->getTopic());
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function set_topic($object)
    {
        $this->topic = new topic($object);
    }

    public function get_opening_time()
    {
        return $this->opening_time;
    }

    public function set_opening_time($opening_time)
    {
        if (($timestamp = strtotime($opening_time)) === false) {
            throw new \moodle_exception('invalid_data', 'local_attendancewebhook');
        } else {
            $this->opening_time = $timestamp;
        }
    }

    public function get_closing_time()
    {
        return $this->closing_time;
    }

    public function set_closing_time($closing_time)
    {
        if (($timestamp = strtotime($closing_time)) === false) {
            throw new \moodle_exception('invalid_data', 'local_attendancewebhook');
        } else {
            $this->closing_time = $timestamp;
        }
    }
    public function get_server_time() {
        return $this->server_time;
    }

    public function set_server_time($server_time) {
        if (($timestamp = strtotime($server_time)) === false) {
            throw new \moodle_exception('invalid_data', 'local_attendancewebhook');
        } else {
            $this->server_time = $timestamp;
        }
    }
    public function get_event_note()
    {
        return $this->event_note;
    }

    public function set_event_note($event_note)
    {
        $this->event_note = isset($event_note) ? $event_note : '';
    }

    public function get_member()
    {
        return $this->member;
    }
    public function set_member($member)
    {
        $this->member = new member($member);
    }
    public function set_mode($mode) {
        $this->mode = $mode;
    }
    public function get_mode() {
        return $this->mode;
    }
    public function get_attendance_note() {
        return $this->attendance_note;
    }

    public function set_attendance_note($attendance_note) {
        $this->attendance_note = $attendance_note;
    }

    public function get_attendances()
    {
        $attendanceobj =(object)[
            'member' => $this->getTopic()->get_member(),
            'mode' => $this->get_mode(),
            'attendanceNote' => $this->get_attendance_note(),
            'serverTime' => date('c', $this->get_server_time()),
        ];
        return [
           new attendance($attendanceobj)
        ];
    }
    public function get_source() {
        return $this->source;
    }
    public function get_logtaker() {
        return $this->get_member();
    }
}
