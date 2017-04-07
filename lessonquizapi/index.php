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
 * External lessonquizapi API
 *
 * @category   external
 * @author     Vikas Sheokand <vikas@virasatsolutions.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2016 onwards Vikas Sheokand  http://virasatsolutions.com/
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/lessonquizapi/locallib.php');

$context = context_system::instance();

require_login();
if (!is_siteadmin()) {
    return '';
}
$lessonquizapi = new local_lessonquizapi();

$PAGE->set_context($context);
$PAGE->set_url('/local/lessonquizapi/index.php.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_lessonquizapi'));
$PAGE->navbar->add(get_string('pluginname', 'local_lessonquizapi'));

$tableheader = array(
    get_string('fieldname', 'local_lessonquizapi'),
    get_string('yourvalue', 'local_lessonquizapi'));

$customfields = $lessonquizapi->customfields;
$customvalues = $lessonquizapi->get_user_custom_values($USER);

// Custom profile Fields.
$tablecustom = new html_table();
$tablecustom->head = $tableheader;

foreach ($customfields as $field) {
    $tablecustom->data[] = array('[['.$field.']]', $customvalues[$field]);
}

// Moodle lessonquizapi template Fields.
$tablelessonquizapi = new html_table();
$tablecustom->head = $tableheader;

foreach ($lessonquizapi->lessonquizapifields as $field) {
    $tablelessonquizapi->data[] = array('[['.$field.']]', $lessonquizapi->lessonquizapivalues[$field]);
}

// Moodle default user template Fields.
$tabledefault = new html_table();
$tabledefault->head = $tableheader;
$userdefaultvalues = $lessonquizapi->get_user_default_values($USER);

foreach ($lessonquizapi->defaultfields as $field) {
    $tabledefault->data[] = array('[['.$field.']]', $userdefaultvalues[$field]);
}

$editurl = new moodle_url('/admin/settings.php', array('section' => 'local_lessonquizapi'));

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('pluginname', 'local_lessonquizapi'));
echo html_writer::tag('p', get_string('globalhelp', 'local_lessonquizapi'));
echo $OUTPUT->single_button($editurl, get_string('configure', 'local_lessonquizapi'));

echo html_writer::tag('h2', get_string('customprofilefields', 'local_lessonquizapi'));
echo html_writer::table($tablecustom);

echo html_writer::tag('h2', get_string('lessonquizapifields', 'local_lessonquizapi'));
echo html_writer::table($tablelessonquizapi);

echo html_writer::tag('h2', get_string('defaultprofilefields', 'local_lessonquizapi'));
echo html_writer::table($tabledefault);
echo $OUTPUT->footer();