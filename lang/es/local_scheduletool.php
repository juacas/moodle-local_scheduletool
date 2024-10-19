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

$string['course_id_description'] = 'ID de curso de moodle correspondiente al ID de actividad de asistencia (topic_id)';
$string['course_id_name'] = 'ID curso';
$string['invalid_data'] = 'Datos inválidos.';
$string['member_id_description'] = 'ID de miembro de asistencia correspondiente al ID de usuario de moodle';
$string['member_id_name'] = 'ID miembro';
$string['module_name_description'] = 'Nombre de módulo de asistencia de moodle';
$string['module_name_name'] = 'Nombre módulo';
$string['module_section_description'] = 'Número de sección de módulo de asistencia de moodle';
$string['module_section_name'] = 'Sección módulo';
$string['notification_attendances'] = 'Asistencias:';
$string['notification_messages'] = 'Resultados:';
$string['notification_contact_admin'] = 'Por favor, contacte con el administrador.';
$string['notification_error_attendances'] = 'No se han podido incorporar las siguientes asistencias a "{$a->event}":';
$string['notification_error_event'] = 'No se ha podido registrar en {$a->event}:';
$string['notification_subject'] = 'Resultados de {$a->topic}';
$string['notifications_enabled_description'] = 'Habilitar o deshabilitar las notificaciones';
$string['notifications_enabled_name'] = 'Notificaciones';
$string['notifications_new_activity'] = 'Se ha creado el módulo de control de asistencia "{$a->activityname}" en el curso "{$a->coursename}".';
$string['notifications_user_unknown_notmarked'] = 'Usuario no marcado por desconocido: {$a}';
$string['pluginname'] = 'Herramientas de horarios';
$string['privacy:metadata:mod_attendance'] = 'El plugin Scheduletool no almacena datos personales.';
$string['tempusers_enabled_description'] = 'Habilitar o deshabilitar la creación de usuarios temporales';
$string['tempusers_enabled_name'] = 'Usuarios temporales';
$string['user_id_description'] = 'ID de usuario de moodle correspondiente al ID de miembro de asistencia';
$string['user_id_name'] = 'ID usuario';

$string['restservices_heading'] = 'Servicios REST';
$string['restservices_description'] = 'Los servicios REST se usan para reportar datos de usuario y lista de temas para cada usuario. Este plugin se usa para integrar con Asistencia de Datio';
$string['restservices_enabled_name'] = 'Activa REST services';
$string['restservices_enabled_description'] = 'Activa o desactiva REST services';
$string['restservices_apikey_name'] = 'API key';
$string['restservices_apikey_description'] = 'API key para autenticación en REST services. Ejemplo aleatorio: {$a}';
$string['restservices_apiuser_name'] = 'API user';
$string['restservices_apiuser_description'] = 'API user para autenticación en REST services';
$string['restservices_fieldNIA'] = 'campo para el NIA';
$string['restservices_fieldNIA_description'] = 'Campo de usuario donde se almacena el NIA';
$string['modattendance_heading'] = 'Integración con mod_attendance';
$string['modattendance_description'] = 'El plugin mod_attendance se usa para gestionar la asistencia en Moodle. Este plugin se puede integrar con Asistencia de Datio';
$string['modattendance_enabled_name'] = 'Habilitar integración con mod_attendance';
$string['modattendance_enabled_description'] = 'Habilitar o deshabilitar la integración con mod_attendance';
$string['modattendance_notavailable'] = 'El plugin mod_attendance no está disponible';
$string['modattendance_notavailable_description'] = 'El plugin mod_attendance se puede usar para gestionar la asistencia en Moodle. Puede encontrarlo en <a href="https://moodle.org/plugins/mod_attendance">https://moodle.org/plugins/mod_attendance</a>';

$string['restservices_useronbehalf'] = 'Ejecutar en nombre de:';
$string['restservices_useronbehalf_description'] = 'Ejecutar los servicios REST en nombre del usuario seleccionado. Debe tener el permiso global "mod/attendance:addinstance" para crear una instancia de mod_attendance para los eventos de curso';
$string['restservices_enableincategories'] = 'Habilitar solo en las siguientes categorías';
$string['restservices_enableincategories_description'] = 'Habilitar los servicios REST solo para los cursos en las categorías seleccionadas y sus subcategorías. Todos los cursos si se deja vacío.';
$string['restservices_disableincategories'] = 'Deshabilitar en las siguientes categorías';
$string['restservices_disableincategories_description'] = 'Deshabilitar los servicios REST para los cursos en las categorías seleccionadas y sus subcategorías. Ningún curso si se deja vacío.';
$string['skip_redirected_courses_name'] = 'Saltar cursos redirigidos';
$string['skip_redirected_courses_description'] = 'No se procesarán los cursos con el course_format: redirected (Ver <a href="https://moodle.org/plugins/format_redirected">https://moodle.org/plugins/format_redirected</a>)';
$string['local_caches_ttl'] = 'Expiración de cachés locales';
$string['local_caches_ttl_description'] = 'Habilitar o deshabilitar las cachés locales del plugin. Las cachés se utilizan para almacenar los datos de los Topics y calendarios de cursos locales por razones de rendimiento. ttl=0 significa sin caché.';
$string['remote_caches_ttl'] = 'Expiración de cachés remotos';
$string['remote_caches_ttl_description'] = 'Habilitar o deshabilitar las cachés remotas del plugin. Las cachés se utilizan para almacenar los datos de los Topics de sistemas remotos por razones de rendimiento. ttl=0 significa sin caché.';
    
$string['export_sessions_as_topics_name'] = 'Exportar sesiones como temas';
$string['export_sessions_as_topics_description'] = 'Las próximas sesiones de asistencia en cualquier instancia de mod_attendance se exportará como un tema';
$string['export_courses_as_topics_name'] = 'Exportar cursos como temas';
$string['export_courses_as_topics_description'] = 'Cada curso se exportará como un tema de almacenamiento. Se creará una nueva actividad de asistencia para cada curso para almacenar las sesiones de asistencia';
$string['modhybridteaching_heading'] = 'Integración con mod_hybridteaching';
$string['modhybridteaching_description'] = 'El plugin mod_hybridteaching se usa para gestionar la asistencia en Moodle. Más información en <a href="https://unimoodle.github.io/moodle-mod_hybridteaching/">https://unimoodle.github.io/moodle-mod_hybridteaching/</a>';
$string['modhybridteaching_enabled_name'] = 'Habilitar integración con mod_hybridteaching';
$string['modhybridteaching_enabled_description'] = 'Habilitar o deshabilitar la integración con mod_hybridteaching';
$string['modhybridteaching_notavailable'] = 'El plugin mod_hybridteaching no está disponible';
$string['modhybridteaching_notavailable_description'] = 'El plugin mod_hybridteaching se puede usar para gestionar la asistencia en Moodle. Puede encontrarlo en <a href="https://unimoodle.github.io/moodle-mod_hybridteaching/">https://unimoodle.github.io/moodle-mod_hybridteaching/</a>';	
$string['field_mapping_heading'] = 'Mapeo de campos';
$string['field_mapping_description'] = 'Mapeo de campos para los datos de usuario';
$string['logs_enabled_name'] = 'Log activos';
$string['logs_enabled_description'] = 'Habilitar o deshabilitar los logs. Los logs se guardan en moodledata/local/scheduletool/logs/mtrace.log y se pueden ver en <a href="{$a}">GetLogs</a>.';

$string['withoutschedule'] = 'Sin información de horario';
$string['withschedule'] = 'Según horario docente';
$string['compact_calendar_name'] = 'Comparctar calendario';
$string['compact_calendar_description'] = 'Compactar calendarios de sesiones usando eventos de repetición con el mismo identificador para todos. Si se desactiva se usará un identificador por sesión.';
$string['copy_schedule'] = 'Control de horarios';
$string['copy_schedule_description'] = 'Copiar horarios oficiales a la actividad de mod_attendance';
$string['copy_shedule_course'] = 'Crea una actividad llamada "{$a->attendancename}"en el curso "{$a->coursename}" y copia los horarios oficiales para hacer seguimiento';
$string['no_timetables'] = 'No hay horarios disponibles';
$string['count_sessions_added'] = 'Se han añadido {$a} sesiones de seguimiento de asistencia.';