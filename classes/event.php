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

class event {

    private $topic;

    private $opening_time;

    private $closing_time;

    private $event_note;

    private $attendances;
    private $source;

    public function __construct($json = null) {
        if ($json === null) {
            return;
        }
        if (is_object($json)) {
            $this->source = json_encode($json);
            $object = $json;
        }
        if (is_string($json)) {
            $this->source = $json;
            $object = json_decode($json);
        }
        if (!is_object($object)) {
            throw new \moodle_exception('invalid_data', 'local_attendancewebhook');
        }
        $this->setTopic($object->topic);
        $this->set_opening_time(clean_param($object->openingTime, PARAM_NOTAGS));
        $this->set_closing_time(clean_param($object->closingTime, PARAM_NOTAGS));
        $this->set_event_note(clean_param($object->eventNote, PARAM_TEXT));
        $this->set_attendances($object->attendances);
    }

    public function __toString() {
        return strval($this->getTopic()) . '-' . date('d-m-Y H:i', $this->get_opening_time());
    }

    public function getTopic() {
        return $this->topic;
    }

    public function setTopic($object) {
        $this->topic = new topic($object);
    }

    public function get_opening_time() {
        return $this->opening_time;
    }

    public function set_opening_time($opening_time) {
        if (($timestamp = strtotime($opening_time)) === false) {
            throw new \moodle_exception('invalid_data', 'local_attendancewebhook');
        } else {
            $this->opening_time = $timestamp;
        }
    }

    public function get_closing_time() {
        return $this->closing_time;
    }

    public function set_closing_time($closing_time) {
        if (($timestamp = strtotime($closing_time)) === false) {
            throw new \moodle_exception('invalid_data', 'local_attendancewebhook');
        } else {
            $this->closing_time = $timestamp;
        }
    }

    public function get_event_note() {
        return $this->event_note;
    }

    public function set_event_note($event_note) {
        $this->event_note = isset($event_note) ? $event_note : '';
    }

    public function get_attendances() {
        return $this->attendances;
    }

    public function set_attendances($attendances) {
        $this->attendances = array();
        foreach ($attendances as &$object) {
            $this->attendances[] = new attendance($object);
        }
    }
    public function get_source() {
        return $this->source;
    }
    public function get_logtaker() {
        return $this->getTopic()->get_member();
    }
}
