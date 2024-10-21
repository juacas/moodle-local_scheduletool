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

/**
 * add_session_form. Form to select sessions from course shedules.
 *
 * @package    local_scheduletool
 * @copyright  2024 University of Valladoild, Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_scheduletool\forms;
use local_scheduletool\course_target;
use moodleform;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Form to select sessions from course shedules.
 */
class add_sessions_form extends moodleform
{
    public function definition()
    {
        $mform = $this->_form;
        $course = $this->_customdata['course'];
        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'course', $course->id);
        $mform->setType('course', PARAM_INT);
        $mform->addElement('date_selector', 'fromdate', get_string('from'));
        $mform->addElement('date_selector', 'todate', get_string('to'));
        // Checkbox for compress calendars.
        $mform->addElement('checkbox', 'compress', get_string('compact_calendar_name', 'local_scheduletool'));
        $mform->setDefault('compress', 0);
        // Button for refresh.
        $mform->addElement('submit', 'refresh', get_string('refresh'));
        // Default dates.
        $mform->setDefault('fromdate', $course->startdate);
        $mform->setDefault('todate', $course->enddate);
        // Heading for calendars.
        $mform->addElement('html', '<h3>' . get_string('copy_schedule', 'local_scheduletool') . '</h3>');        
        // Get course calendar.
        $course = $this->_customdata['course'];
        $compress = $this->_customdata['compress'];
        $fromdate = $this->_customdata['fromdate'];
        $todate = $this->_customdata['todate'];
        $fromdate = $fromdate ? strtotime("{$fromdate['day']}-{$fromdate['month']}-{$fromdate['year']}"): $course->startdate;
        $todate = $todate ? strtotime("{$todate['day']}-{$todate['month']}-{$todate['year']}"): $course->enddate;

        $calendars = course_target::get_course_calendars($course,
                 null,
                 $fromdate,
                 $todate,
                 $compress);
        $id = 0;
       
        // Create a form checkbox for each calendar that has timetables.
        foreach ($calendars as $calendar) {
            if (empty($calendar->timetables)) {
                continue;
            }
            foreach ($calendar->timetables as $timetable) {
                $weekstart = date('W', strtotime($calendar->startDate));
                $weekend = date('W', strtotime($calendar->endDate));
                // Format name from calendar timetable.
                if ($weekend == $weekstart) {
                    $name = 'Semana ' . date('Y-m-d', strtotime($calendar->startDate)) . ': '. $timetable->weekdays;
                } else {
                    $name = "Semana del {$calendar->startDate} al {$calendar->endDate}: {$timetable->weekdays}";
                }
                $name .= " {$timetable->startTime} - {$timetable->endTime} {$timetable->info} ";
                $id = base64_encode(json_encode($calendar));
                
                $mform->addElement('advcheckbox', $id, $name);
                $id++;                
            }
        }
        if ($id == 0)  {
            $mform->addElement('html', '<p>' . get_string('no_timetables', 'local_scheduletool') . '</p>');
        } else {
            $mform->addElement('submit', 'submit', get_string('copy_schedule', 'local_scheduletool'));
        }
        $mform->addElement('cancel', 'cancel', get_string('cancel'));
    }
}
