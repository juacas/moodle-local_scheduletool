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

class member {

    private $username;

    private $firstname;

    private $lastname;

    private $email;

    public function __construct($object) {
        if (!is_object($object)) {
            throw new \moodle_exception('invalid_data', 'local_attendancewebhook');
        }
        $this->set_username(clean_param($object->username, PARAM_NOTAGS));
        $this->set_firstname(clean_param($object->firstname, PARAM_NOTAGS));
        $this->set_lastname(clean_param($object->lastname, PARAM_NOTAGS));
        $this->set_email(clean_param($object->email, PARAM_EMAIL));
    }

    public function __toString() {
        return $this->get_username() . ' - ' . $this->get_firstname() . ' ' . $this->get_lastname();
    }

    public function get_username() {
        return $this->username;
    }

    public function set_username($username) {
        if (empty(trim($username))) {
            throw new \moodle_exception('invalid_data', 'local_attendancewebhook');
        } else {
            $this->username = $username;
        }
    }

    public function get_firstname() {
        return $this->firstname;
    }

    public function set_firstname($firstname) {
        $this->firstname = $firstname;
    }

    public function get_lastname() {
        return $this->lastname;
    }

    public function set_lastname($lastname) {
        $this->lastname = $lastname;
    }

    public function get_email() {
        return $this->email;
    }

    public function set_email($email) {
        if (empty(trim($email))) {
            throw new \moodle_exception('invalid_data', 'local_attendancewebhook');
        } else {
            $this->email = $email;
        }
    }

}
