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

class topic {

    private $topic_id;

    public string $name;

    private $type;

    private $member;

    public function __construct($object) {
        if (!is_object($object)) {
            throw new \moodle_exception('invalid_data', 'local_scheduletool');
        }
        $this->set_topic_id(clean_param($object->topicId, PARAM_NOTAGS));
        $this->setName(clean_param($object->name, PARAM_TEXT));
        $this->set_type(clean_param($object->type, PARAM_NOTAGS));
        $this->set_member($object->member);
    }

    public function __toString() {
        return $this->getName();
    }

    public function get_topic_id() {
        return $this->topic_id;
    }

    public function set_topic_id($topic_id) {
        $this->topic_id = $topic_id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function get_type() {
        return $this->type;
    }

    public function set_type($type) {
        $this->type = $type;
    }

    public function get_member() {
        return $this->member;
    }

    public function set_member($member) {
        $this->member = new member($member);
    }

}
