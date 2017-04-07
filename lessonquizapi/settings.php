<?php
// This file is part of the Local lessonquizapi plugin
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
 * External lessonquizapi API Settings Page
 *
 * @subpackage lessonquizapi
 * @copyright  2015 Bas Brands, basbrands.nl, bas@sonsbeekmedia.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $moderator = get_admin();
    $site = get_site();

    $settings = new admin_settingpage('local_lessonquizapi', get_string('pluginname', 'local_lessonquizapi'));
    $ADMIN->add('localplugins', $settings);

    $name = 'local_lessonquizapi/files_token';
    $default = '';
    $title = get_string('files_token_key', 'local_lessonquizapi');
    $description = get_string('files_token_description', 'local_lessonquizapi');
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);
}

