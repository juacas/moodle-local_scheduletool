<?php
namespace local_attendancewebhook\tests\phpunit;

use local_attendancewebhook\course_target;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/config.php');


class attendancewebhook_course_get_calendar_test extends \advanced_testcase {

    const COURSE_START = 1609459200; // 2021-01-01 00:00:00
    const COURSE_END = 1640995200; // 2022-01-01 00:00:00

    const COURSE_FULLNAME = 'Test Course';
    
    protected $course = null;
    protected $coursecontext = null;
    protected $user = null;
    protected $config = null;

    public function setUp(): void {
        global $USER;
        parent::setUp();
        $this->resetAfterTest(true);
        self::setAdminUser();
        self::$course = self::getDataGenerator()->create_course(
            ['fullname' => self::COURSE_FULLNAME, 'startdate' => self::COURSE_START, 'enddate' => self::COURSE_END]
        );
        self::$coursecontext = \context_course::instance(self::$course->id);
        self::$user = $USER;
        self::$config = 0;
    }

    public function test_get_calendar() {
        global $DB;
        $calendars  = course_target::parse_course_calendars($this->course, self::JSONCALENDARAPPCRUE );
        $this->assertTrue(count($calendars) > 0);
    }

    const JSONCALENDARAPPCRUE = '[
        {
            "day": "20220324",
            "schedule": [
                {
                    "starts": "09:00",
                    "ends": "11:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE TEORÍA DEL CONOCIMIENTO",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "09:00",
                    "ends": "10:00",
                    "location": "AULA 201 \"JUAN DE LA ENCINA\"",
                    "subjectname": "FILOSOFÍA DE LOS SIGLOS XX-XXI",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "11:00",
                    "ends": "12:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE ESTÉTICA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "12:00",
                    "ends": "13:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "CUESTIONES DE FILOSOFÍA POLÍTICA CONTEMPORÁNEA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                }
            ]
        },
        {
            "day": "20220328",
            "schedule": [
                {
                    "starts": "09:00",
                    "ends": "10:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "CUESTIONES DE FILOSOFÍA POLÍTICA CONTEMPORÁNEA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "10:00",
                    "ends": "11:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE ESTÉTICA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "10:00",
                    "ends": "12:00",
                    "location": "AULA 201 \"JUAN DE LA ENCINA\"",
                    "subjectname": "FILOSOFÍA DE LOS SIGLOS XX-XXI",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "11:00",
                    "ends": "13:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "METAFÍSICA II",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                }
            ]
        },
        {
            "day": "20220329",
            "schedule": [
                {
                    "starts": "09:00",
                    "ends": "11:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "CUESTIONES DE FILOSOFÍA POLÍTICA CONTEMPORÁNEA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "11:00",
                    "ends": "12:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE TEORÍA DEL CONOCIMIENTO",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "12:00",
                    "ends": "13:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "METAFÍSICA II",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                }
            ]
        },
        {
            "day": "20220330",
            "schedule": [
                {
                    "starts": "09:00",
                    "ends": "11:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE ESTÉTICA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "11:00",
                    "ends": "12:00",
                    "location": "AULA 201 \"JUAN DE LA ENCINA\"",
                    "subjectname": "FILOSOFÍA DE LOS SIGLOS XX-XXI",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "11:00",
                    "ends": "12:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE TEORÍA DEL CONOCIMIENTO",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "12:00",
                    "ends": "13:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "METAFÍSICA II",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                }
            ]
        },
        {
            "day": "20220331",
            "schedule": [
                {
                    "starts": "09:00",
                    "ends": "10:00",
                    "location": "AULA 201 \"JUAN DE LA ENCINA\"",
                    "subjectname": "FILOSOFÍA DE LOS SIGLOS XX-XXI",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "09:00",
                    "ends": "11:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE TEORÍA DEL CONOCIMIENTO",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "11:00",
                    "ends": "12:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE ESTÉTICA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "12:00",
                    "ends": "13:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "CUESTIONES DE FILOSOFÍA POLÍTICA CONTEMPORÁNEA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                }
            ]
        },
        {
            "day": "20220404",
            "schedule": [
                {
                    "starts": "09:00",
                    "ends": "10:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "CUESTIONES DE FILOSOFÍA POLÍTICA CONTEMPORÁNEA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "10:00",
                    "ends": "11:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE ESTÉTICA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "10:00",
                    "ends": "12:00",
                    "location": "AULA 201 \"JUAN DE LA ENCINA\"",
                    "subjectname": "FILOSOFÍA DE LOS SIGLOS XX-XXI",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "11:00",
                    "ends": "13:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "METAFÍSICA II",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                }
            ]
        },
        {
            "day": "20220405",
            "schedule": [
                {
                    "starts": "09:00",
                    "ends": "11:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "CUESTIONES DE FILOSOFÍA POLÍTICA CONTEMPORÁNEA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "11:00",
                    "ends": "12:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE TEORÍA DEL CONOCIMIENTO",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "12:00",
                    "ends": "13:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "METAFÍSICA II",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                }
            ]
        },
        {
            "day": "20220406",
            "schedule": [
                {
                    "starts": "09:00",
                    "ends": "11:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE ESTÉTICA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "11:00",
                    "ends": "12:00",
                    "location": "AULA 201 \"JUAN DE LA ENCINA\"",
                    "subjectname": "FILOSOFÍA DE LOS SIGLOS XX-XXI",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "11:00",
                    "ends": "12:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE TEORÍA DEL CONOCIMIENTO",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "12:00",
                    "ends": "13:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "METAFÍSICA II",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                }
            ]
        },
        {
            "day": "20220407",
            "schedule": [
                {
                    "starts": "09:00",
                    "ends": "10:00",
                    "location": "AULA 201 \"JUAN DE LA ENCINA\"",
                    "subjectname": "FILOSOFÍA DE LOS SIGLOS XX-XXI",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "09:00",
                    "ends": "11:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE TEORÍA DEL CONOCIMIENTO",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "11:00",
                    "ends": "12:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "TEMAS DE ESTÉTICA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                },
                {
                    "starts": "12:00",
                    "ends": "13:00",
                    "location": "AULA 5 \"VICTOR HUGO\"",
                    "subjectname": "CUESTIONES DE FILOSOFÍA POLÍTICA CONTEMPORÁNEA",
                    "degreename": "GRADO EN FILOSOFÍA",
                    "groupname": "1T1A"
                }
            ]
        }
    ]';
}
