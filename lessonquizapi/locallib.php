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
 * @package local_lessonquizapi
 * @author     Vikas Sheokand <vikas@virasatsolutions.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2016 onwards Vikas Sheokand  http://virasatsolutions.com/
 */

require_once('../../config.php');
$filestoken = get_config('local_lessonquizapi', 'files_token');
define('FILES_TOKEN', $filestoken);
/**
 * Authenticate user
 *
 * @param string $urlusername access username
 * @param string $urlpassword access password
 * @return array
 */
function lessonquizapi_wsauthenticate($urlusername, $urlpassword) {
    global $CFG, $DB;
    $arr = array();
    $returndata = array();
    $un = filter_var($urlusername, FILTER_VALIDATE_EMAIL);

    if (!$un) {
        $arr['message1'] = 'Please provide valid email address !';
        $arr['message2'] = false;
        $returndata = $arr;
    } else {
        $un = $urlusername;
        $userdetails = authenticate_user_login($un, $urlpassword);
        if ( $userdetails ) {
            $sql = "SELECT id, username, firstname, lastname, email
        	          FROM {user}
                     WHERE username = :username";
            $user = $DB->get_record_sql($sql, array('username' => $urlusername));

            $arr['username'] = $user->username;
            if (empty($user->email)) {
                $arr['message1'] = 'Before Access your Web Service API, Update your Profile!';
                $arr['message2'] = false;
            } else {
                $arr['message1'] = 'User Authenticated Sucessfully!';
                $arr['message2'] = true;
            }
            $returndata[] = $arr;
        } else {
            require_once($CFG->dirroot.'/lib/moodlelib.php');
            $sql = "SELECT id
                      FROM {user}
                     WHERE username = :username AND email = :email";
            $userrec = $DB->get_record_sql($sql, array('username' => $urlusername, 'email' => $urlusername));

            if (empty($userrec)) {
                $returnobj['message1'] = 'You are not a Valid User!';
                $returnobj['message2'] = false;
            } else {
                $returnobj['username'] = $urlusername;
                $returnobj['message1'] = 'Username and Password not matched !';
                $returnobj['message2'] = false;
            }
            $returndata[] = $returnobj;
        }

        return $returndata;
    }
    $returndata[] = $arr;
    return $returndata;
}

/**
 * Authenticate/ Create User
 *
 * @param string $urlusername access username
 * @param string $urlpassword access password
 * @return array
 */
function lessonquizapi_wsauthenticate_create($urlusername, $urlpassword) {
    global $CFG, $DB;
    $arr = array();
    $returndata = array();
    $un = filter_var($urlusername, FILTER_VALIDATE_EMAIL);

    if (!$un) {
        $arr['message1'] = 'Please provide valid email address !';
        $arr['message2'] = false;
        $returndata = $arr;
    } else {
        $un = $urlusername;
        $userdetails = authenticate_user_login($un, $urlpassword);
        if ( $userdetails ) {
            $sql = "SELECT id, username, firstname, lastname, email
                      FROM {user}
                     WHERE username = :username";
            $user = $DB->get_record_sql($sql, array('username' => $urlusername));

            $arr['username'] = $user->username;
            if (empty($user->email)) {
                $arr['message1'] = 'Before Access your Web Service API, Update your Profile!';
                $arr['message2'] = false;
            } else {
                $arr['message1'] = 'User Authenticated Sucessfully!';
                $arr['message2'] = true;
            }
            $returndata[] = $arr;
        } else {
            require_once($CFG->dirroot.'/lib/moodlelib.php');
            $sql = "SELECT id
                      FROM {user}
                     WHERE username = :username AND email = :email";
            $userrec = $DB->get_record_sql($sql, array('username' => $urlusername, 'email' => $urlusername));

            if (empty($userrec)) {
                $userobj = create_user_record($urlusername, $urlpassword);
                $returnobj['username'] = $userobj->username;
                $returnobj['message1'] = 'User Created Successfully please update your profile!';
                $returnobj['message2'] = true;

                $record = new stdClass();
                $record->id = $userobj->id;
                $record->firstname = 'No Firstname';
                $record->lastname  = 'No Lastname';
                $record->email     = $urlusername;
                $DB->update_record('user', $record);
            } else {
                $returnobj['username'] = $urlusername;
                $returnobj['message1'] = 'Username and Password not matched !';
                $returnobj['message2'] = false;
            }
            $returndata[] = $returnobj;
        }

        return $returndata;
    }
    $returndata[] = $arr;
    return $returndata;
}

/**
 * explode string by delimiters
 *
 * @param string $delimiters delimiters
 * @param string $string string
 * @return string
 */
function multiexplode ($delimiters, $string) {
    $ready  = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return $launch;
}

/**
 * Get lesson images
 *
 * @param string $contents data
 * @return string
 */
function lessonimages($contents) {
    global $DB, $CFG;
    $explod1 = multiexplode(array('<img src="', '>'), $contents);
    $a = array();
    foreach ($explod1 as $val) {
        if (stripos($val, '.png') OR stripos($val, '.jpg') OR stripos($val, '.jpeg') OR stripos($val, '.gif')) {
            $a[] = $val;
        }
    }

    $explod3 = multiexplode(array('<a href="', '<'), $contents);
    foreach ($explod3 as $val) {
        if (stripos($val, '.flv') OR stripos($val, '.mp4') OR stripos($val, '.swf')) {
            $a[] = $val;
        }
    }
    $explod2 = multiexplode(array('<img src="@@PLUGINFILE@@/', '" alt'), $contents);
    $b = array();
    $i = 0;
    foreach ($explod2 as $val) {
        if (stripos($val, '.png') OR stripos($val, '.jpg') OR stripos($val, '.jpeg')  OR stripos($val, '.gif')) {
            $b[] = $val;
            $i++;
        }
    }

    $explod4 = multiexplode(array('<a href="@@PLUGINFILE@@/', '<', 'a href="@@PLUGINFILE@@/'), $contents);
    foreach ($explod4 as $val) {
        if (stripos($val, '.flv') OR stripos($val, '.mp4') OR stripos($val, '.swf')) {
            $b[] = $val;
            $i++;
        }
    }
    $imgfile = array();
    for ($j = 0; $j <= $i - 1; $j++) {
        if (stripos($b[$j], '.flv') OR stripos($b[$j], '.mp4') OR stripos($b[$j], '.swf')) {
            $asd = explode('">', $b[$j]);
            $img = $asd[0];
        } else {
            $img = $b[$j];
        }
        $component = 'mod_lesson';
        $sqla = "SELECT f.contextid as contex, f.filearea as fname, f.itemid as item, f.timecreated as ctime,
                        f.timemodified as mtime
                   FROM {files} f
             INNER JOIN {context} c ON f.contextid = c.id
                  WHERE f.filename  LIKE :img AND f.component = :component";
        $record = $DB->get_recordset_sql($sqla, array('img' => "%$img%", 'component' => $component));

        foreach ($record as $rec) {
            $contextid = $rec->contex;
            $filearea = $rec->fname;
            $itemid = $rec->item;
        }
        $imgfile[] = $CFG->wwwroot."/webservice/pluginfile.php/$contextid/$component/$filearea/$itemid/$img?token=".FILES_TOKEN;
    }
    $newcontents = $contents;
    for ($k = 0; $k <= $i - 1; $k++) {
        if (stripos($a[$k], '.flv') OR stripos($a[$k], '.mp4') OR stripos($a[$k], '.swf')) {
            $newcontents = str_replace( '<'.$a[$k].'</a>', '<a target="_blank" href="'.$imgfile[$k].'">video</a>',
                            $newcontents);
        } else {
            $newcontents = str_replace( '"'.$a[$k], $imgfile[$k], $newcontents);
        }
    }
    $data = $newcontents;
    return($data);
}

/**
 * Get quiz images
 *
 * @param string $contents data
 * @param int $questionid question id
 * @return string
 */
function quizimages($contents, $questionid) {
    global $DB, $CFG;
    $explod1 = multiexplode(array('<img src="', '>'), $contents);
    $a = array();
    foreach ($explod1 as $val) {
        if (stripos($val, '.png') OR stripos($val, '.jpg') OR stripos($val, '.jpeg') OR stripos($val, '.gif')) {
            $a[] = $val;
        }
    }

    $explod3 = multiexplode(array('<a href="', '<'), $contents);
    foreach ($explod3 as $val) {
        if (stripos($val, '.flv') OR stripos($val, '.mp4') OR stripos($val, '.swf')) {
            $a[] = $val;
        }
    }
    $explod2 = multiexplode(array('<img src="@@PLUGINFILE@@/', '" alt'), $contents);
    $b = array();
    $i = 0;
    foreach ($explod2 as $val) {
        if (stripos($val, '.png') OR stripos($val, '.jpg') OR stripos($val, '.jpeg') OR stripos($val, '.gif')) {
            $b[] = $val;
            $i++;
        }
    }

    $explod4 = multiexplode(array('<a href="@@PLUGINFILE@@/', '<', 'a href="@@PLUGINFILE@@/'), $contents);
    foreach ($explod4 as $val) {
        if (stripos($val, '.flv') OR stripos($val, '.mp4') OR stripos($val, '.swf')) {
            $b[] = $val;
            $i++;
        }
    }
    $imgfile = array();
    for ($j = 0; $j <= $i - 1; $j++) {
        if (stripos($b[$j], '.flv') OR stripos($b[$j], '.mp4') OR stripos($b[$j], '.swf')) {
            $asd = explode('">', $b[$j]);
            $img = $asd[0];
        } else {
            $img = $b[$j];
        }

        $sqlx = "SELECT questionusageid, slot
                   FROM {question_attempts}
                  WHERE questionid = :questionid";
        $res = $DB->get_record_sql($sqlx, array('questionid' => $questionid));
        $questionusageid = $res->questionusageid;
        $slot = $res->slot;

        $component = 'question';
        $sqla = "SELECT f.contextid as contex, f.filearea as fname, f.itemid as item
                   FROM {files} f
             INNER JOIN {context} c ON f.contextid = c.id
                  WHERE f.filename  LIKE :filename AND f.component = :component";
        $record = $DB->get_recordset_sql($sqla, array('filename' => "%$img%", 'component' => $component));

        foreach ($record as $rec) {
            $contextid = $rec->contex;
            $filearea = $rec->fname;
            $itemid = $rec->item;
        }
        $imgfile[] = $CFG->wwwroot.
        "/webservice/pluginfile.php/$contextid/$component/$filearea/$questionusageid/$slot/$itemid/$img?token=".FILES_TOKEN;
    }
    $newcontents = $contents;
    for ($k = 0; $k <= $i - 1; $k++) {
        if (stripos($a[$k], '.flv') OR stripos($a[$k], '.mp4') OR stripos($a[$k], '.swf')) {
            $newcontents = str_replace( '<'.$a[$k].'</a>', '<a target="_blank" href="'.$imgfile[$k].'">video</a>',
                           $newcontents);
        } else {
            $newcontents = str_replace( '"'.$a[$k], $imgfile[$k], $newcontents);
        }
    }
    $data = $newcontents;
    return($data);
}