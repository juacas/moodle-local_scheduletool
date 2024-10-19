<?php

require_once('../../../config.php');

$json = '[
    {
        "day": "20241009",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "11:00",
                "location": "AULA 014  ( E.T.S. INGENIEROS DE TELECOMUNICACIONES )",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2T"
            }
        ]
    },
    {
        "day": "20241010",
        "schedule": [
            {
                "starts": "16:00",
                "ends": "17:00",
                "location": "AULA 015",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "3S"
            },
            {
                "starts": "17:00",
                "ends": "18:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "3L"
            },
            {
                "starts": "18:00",
                "ends": "19:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "4L"
            }
        ]
    },
    {
        "day": "20241011",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "10:00",
                "location": "AULA 014  ( E.T.S. INGENIEROS DE TELECOMUNICACIONES )",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2S"
            },
            {
                "starts": "10:00",
                "ends": "11:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2L"
            },
            {
                "starts": "11:00",
                "ends": "12:00",
                "location": "AULA 012 (EDIFICIO DE TECNOLOGÍAS DE LA INFORMACIÓN)",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "1S"
            },
            {
                "starts": "12:00",
                "ends": "13:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "1L"
            }
        ]
    },
    {
        "day": "20241015",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "11:00",
                "location": "AULA 012 (EDIFICIO DE TECNOLOGÍAS DE LA INFORMACIÓN)",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "1T"
            }
        ]
    },
    {
        "day": "20241016",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "11:00",
                "location": "AULA 014  ( E.T.S. INGENIEROS DE TELECOMUNICACIONES )",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2T"
            }
        ]
    },
    {
        "day": "20241017",
        "schedule": [
            {
                "starts": "16:00",
                "ends": "17:00",
                "location": "AULA 015",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "3S"
            },
            {
                "starts": "17:00",
                "ends": "18:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "3L"
            },
            {
                "starts": "18:00",
                "ends": "19:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "4L"
            }
        ]
    },
    {
        "day": "20241018",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "10:00",
                "location": "AULA 014  ( E.T.S. INGENIEROS DE TELECOMUNICACIONES )",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2S"
            },
            {
                "starts": "10:00",
                "ends": "11:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2L"
            },
            {
                "starts": "11:00",
                "ends": "12:00",
                "location": "AULA 012 (EDIFICIO DE TECNOLOGÍAS DE LA INFORMACIÓN)",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "1S"
            },
            {
                "starts": "12:00",
                "ends": "13:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "1L"
            }
        ]
    },
    {
        "day": "20241022",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "11:00",
                "location": "AULA 012 (EDIFICIO DE TECNOLOGÍAS DE LA INFORMACIÓN)",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "1T"
            }
        ]
    },
    {
        "day": "20241023",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "11:00",
                "location": "AULA 014  ( E.T.S. INGENIEROS DE TELECOMUNICACIONES )",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2T"
            }
        ]
    },
    {
        "day": "20241024",
        "schedule": [
            {
                "starts": "16:00",
                "ends": "17:00",
                "location": "AULA 015",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "3S"
            },
            {
                "starts": "17:00",
                "ends": "18:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "3L"
            },
            {
                "starts": "18:00",
                "ends": "19:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "4L"
            }
        ]
    },
    {
        "day": "20241025",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "10:00",
                "location": "AULA 014  ( E.T.S. INGENIEROS DE TELECOMUNICACIONES )",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2S"
            },
            {
                "starts": "10:00",
                "ends": "11:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2L"
            },
            {
                "starts": "11:00",
                "ends": "12:00",
                "location": "AULA 012 (EDIFICIO DE TECNOLOGÍAS DE LA INFORMACIÓN)",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "1S"
            },
            {
                "starts": "12:00",
                "ends": "13:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "1L"
            }
        ]
    },
    {
        "day": "20241029",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "11:00",
                "location": "AULA 012 (EDIFICIO DE TECNOLOGÍAS DE LA INFORMACIÓN)",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "1T"
            }
        ]
    },
    {
        "day": "20241030",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "11:00",
                "location": "AULA 014  ( E.T.S. INGENIEROS DE TELECOMUNICACIONES )",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2T"
            }
        ]
    },
    {
        "day": "20241031",
        "schedule": [
            {
                "starts": "16:00",
                "ends": "17:00",
                "location": "AULA 015",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "3S"
            },
            {
                "starts": "17:00",
                "ends": "18:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "3L"
            },
            {
                "starts": "18:00",
                "ends": "19:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "4L"
            }
        ]
    },
    {
        "day": "20241101",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "10:00",
                "location": "AULA 014  ( E.T.S. INGENIEROS DE TELECOMUNICACIONES )",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2S"
            },
            {
                "starts": "10:00",
                "ends": "11:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2L"
            },
            {
                "starts": "11:00",
                "ends": "12:00",
                "location": "AULA 012 (EDIFICIO DE TECNOLOGÍAS DE LA INFORMACIÓN)",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "1S"
            },
            {
                "starts": "12:00",
                "ends": "13:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "1L"
            }
        ]
    },
    {
        "day": "20241105",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "11:00",
                "location": "AULA 012 (EDIFICIO DE TECNOLOGÍAS DE LA INFORMACIÓN)",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "1T"
            }
        ]
    },
    {
        "day": "20241106",
        "schedule": [
            {
                "starts": "09:00",
                "ends": "11:00",
                "location": "AULA 014  ( E.T.S. INGENIEROS DE TELECOMUNICACIONES )",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "2T"
            }
        ]
    },
    {
        "day": "20241107",
        "schedule": [
            {
                "starts": "16:00",
                "ends": "17:00",
                "location": "AULA 015",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "3S"
            },
            {
                "starts": "17:00",
                "ends": "18:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "3L"
            },
            {
                "starts": "18:00",
                "ends": "19:00",
                "location": "LABORATORIO 2L008",
                "subjectname": "TEORÍA DE LA COMUNICACIÓN",
                "degreename": "GRADO EN INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN, GRADO EN INGENIERÍA DE TECNOLOGÍAS ESPECÍFICAS DE TELECOMUNICACIÓN, PROGRAMA DE DOBLE TITULACIÓN OFICIAL: GRADO EN   INGENIERÍA DE TECNOLOGÍAS DE TELECOMUNICACIÓN Y GRADO EN ADMINISTRACIÓN Y DIRECCIÓN DE EMPRESAS (ITTADE)",
                "groupname": "4L"
            }
        ]
    }
]';
$course = new stdClass();
$course->id = 1;
$course->shortname = 'TC';
$course->fullname = 'TEORIA DE LA COMUNICACION (1-211-512-46612-2-2024)';

$calendars = local_scheduletool\course_target::parse_course_calendars($course, $json);