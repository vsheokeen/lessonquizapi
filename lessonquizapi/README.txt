copyright  2017  Vikas Sheokand http://virasatsolutions.com
author     Vikas Sheokand <vikas@virasatsolutions.com>
license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


version 2017040700:

Added an option to select the authentication methods that will trigger a 
lessonquizapi message. This way you can use the lessonquizapi plugin for manual authentication
an disable it for (for example) email based self registration.

ABOUT

This plugin for Moodle sends a configurable lessonquizapi message to new users.

The plugin get username and password for API authentication return the lesson and quiz question, answers, images url and flash url (.flv, .swf, .mp4) files.

SETTINGS

This local plugin allows you to configure:

This plugin configure Web Services Token


INSTALLATION

Just place the lessonquizapi directory inside your Moodle's local directory.
Install the plugin and browse to:

Site Administration->Plugins->Local plugins->Moodle lessonquizapi

Information
-----------
This is local_lessonquizapi plugin for moodle web services get quiz questions and lesson details with include images.
In this plugin we set Web Services Token in lessonquizapi settings page for access download file.
In this plugin user can access file, check the checkbox for download moodle files. In plugins > web services > External services
Edit your webservices, and check checkbox in Can download files.
In Image or media file name does not contain space or special symbol in filename.