<?php
// This file is part of custom local lessonquizapi plugin".
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
 * @package local_lessonquizapi
 * @author  Vikas Sheokand <vikas@virasatsolutions.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2016 onwards Vikas Sheokand  http://virasatsolutions.com/
 *
 * The notifies block
 */

defined('MOODLE_INTERNAL') || die();

$services = array(
    'lessonquizapi_services' => array(
    'functions' => array ('local_lessonquizapi_total_flash', 'local_lessonquizapi_services', 'local_lessonquizapi_flash_weekly',
        'local_lessonquizapi_flash_batch', 'local_lessonquizapi_user_authenticate', 'local_lessonquizapi_get_lessonquestions',
        'local_lessonquizapi_get_questionanswer', 'local_lessonquizapi_get_quizdetails',
        'local_lessonquizapi_get_quizquestiondetails', 'local_lessonquizapi_get_quizquestions',
        'local_lessonquizapi_get_courselessons', 'local_lessonquizapi_get_lessonquestionbyid',
        'local_lessonquizapi_get_totallessonquestion', 'local_lessonquizapi_get_totalquizquestion',
        'local_lessonquizapi_get_quizdetailswithoutquizid', 'local_lessonquizapi_get_lessquestionwithoutlessonid'),
    'requiredcapability' => '',
    'restrictedusers' => 0,
    'enabled' => 1,
    )
);

$functions = array(
    'local_lessonquizapi_total_flash' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'     => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'total_flash',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_services' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'     => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'flash_videos',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_flash_weekly' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'     => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'flash_weekly',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_flash_batch' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'     => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'flash_batch',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => array('read', 'write')
    ),

    'local_lessonquizapi_user_authenticate' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'     => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'user_authenticate',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_get_lessonquestions' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'   => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'get_lessonquestions',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_get_questionanswer' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'   => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'get_lessquestion',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_get_quizdetails' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'   => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'get_quizdetails',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_get_quizdetailswithoutquizid' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'   => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'get_quizdetailswithoutquizid',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_get_quizquestiondetails' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'   => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'get_quizquestiondetails',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_get_quizquestions' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'   => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'get_quizquestions',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_get_courselessons' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'   => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'get_courselessons',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_get_lessonquestionbyid' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'   => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'get_lessonquestionbyid',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_get_totallessonquestion' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'   => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'get_totallessonquestion',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_get_totalquizquestion' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'   => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'get_totalquizquestion',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    ),

    'local_lessonquizapi_get_lessquestionwithoutlessonid' => array(
    'classname'     => 'lessonquizapi_external',
    'classpath'   => 'local/lessonquizapi/externallib.php',
    'methodname'    => 'get_lessquestionwithoutlessonid',
    'description'   => 'Trigger the course module viewed event.',
    'type'          => 'read'
    )
);

