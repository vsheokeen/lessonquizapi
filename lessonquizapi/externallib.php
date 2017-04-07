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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir."/externallib.php");
$filestoken = get_config('local_lessonquizapi', 'files_token');
define('FILES_TOKEN', $filestoken);
/**
 * This lessonquizapi_external custom web services class
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright (C) 2016 onwards Vikas Sheokand  http://virasatsolutions.com/
 */
class lessonquizapi_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return user_authenticate_parameters
     */
    public static function user_authenticate_parameters() {
        return new external_function_parameters(
            array('username' => new external_value(PARAM_RAW, 'the username'),
                  'password' => new external_value(PARAM_RAW, 'the password')
                 )
        );
    }

    /**
     * Check User Authentication or Create User
     *
     * @param string $username access username
     * @param string $password access password
     * @return array
     */
    public static function user_authenticate($username, $password) {
        global $CFG, $DB;
        $params = array('username' => $username,
                        'password' => $password);

        $params = self::validate_parameters(self::user_authenticate_parameters(), $params);
        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate_create($params['username'], $params['password']);
        $ary = array();

        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            $returnvalue['message'] = $ary['false'];
            return $returnvalue;
        } else {
            $returnvalue['message'] = $ary['false'];
            return $returnvalue;
        }

        return $returndata;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function user_authenticate_returns() {
        return new external_function_parameters(
            array('message' => new external_value(PARAM_RAW, 'message'),
                 )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return total_flash_parameters
     */
    public static function total_flash_parameters() {
        return new external_function_parameters(
            array('username' => new external_value(PARAM_RAW, 'username'),
                  'password' => new external_value(PARAM_RAW, 'password'),
                  'cid' => new external_value(PARAM_RAW, 'course id', VALUE_OPTIONAL)
                 )
        );
    }

    /**
     * Get total flash file url
     *
     * @param string $username access username
     * @param string $password access password
     * @param int $cid courseid
     * @return array
     */
    public static function total_flash($username, $password, $cid) {
        $params = self::validate_parameters(self::total_flash_parameters(),
                      array('username' => $username,
                            'password' => $password,
                            'cid' => $cid));

        global $DB, $CFG;
        $cid = $params[cid];

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);
        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            $sqlab = "SELECT count(id) as id FROM {course}";
            $counts = $DB->get_record_sql($sqlab);
            $a = array();
            foreach (range(1, $counts->id) as $n) {
                $a[] = $n;
            }
            $allcourse = implode(',', $a);
            if ($params[cid]) {
                $courseid = $params[cid];
            } else {
                $courseid = $allcourse;
            }

            $sql = 'SELECT count(p.contents) as fcount
                      FROM {lesson_pages} p
                INNER JOIN {lesson} l ON l.id = p.lessonid
                INNER JOIN {course} c ON c.id = l.course
                INNER JOIN {course_categories} cc ON cc.id = c.category
                     WHERE c.id IN ( '.$courseid.' ) AND (contents LIKE "%.flv%" OR contents LIKE "%.swf%"
                           OR contents LIKE "%.mp4%")';

            $fcount       = $DB->get_record_sql($sql);
            $totalrecord  = $fcount->fcount;
            $datareturn   = array();
            $datareturn[] = array('total_record_flash_file' => $totalrecord );

            return $datareturn;
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a;
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function total_flash_returns() {
        return new external_multiple_structure(
        new external_single_structure(
            array('total_record_flash_file' => new external_value(PARAM_RAW, 'flash url', VALUE_OPTIONAL),
                  'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL)
                 ), 'course'
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return flash_videos_parameters
     */
    public static function flash_videos_parameters() {
        return new external_function_parameters(
            array('username' => new external_value(PARAM_RAW, 'username'),
                  'password' => new external_value(PARAM_RAW, 'password'),
                  'cid' => new external_value(PARAM_RAW, 'course id', VALUE_OPTIONAL)
                 )
        );
    }

    /**
     * Get flash file url
     *
     * @param string $username access username
     * @param string $password access password
     * @param int $cid courseid
     * @return array
     */
    public static function flash_videos($username, $password, $cid) {
        $params = self::validate_parameters(self::flash_videos_parameters(),
                      array('username' => $username,
                            'password' => $password,
                            'cid' => $cid));

        global $DB, $CFG;
        $cid = $params[cid];

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);

        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            $sqlab = "SELECT count(id) as id FROM {course}";
            $counts = $DB->get_record_sql($sqlab);
            $a      = array();

            foreach (range(1, $counts->id) as $n) {
                $a[] = $n;
            }
            $allcourse = implode(',', $a);
            if ($params[cid]) {
                $courseid = $params[cid];
            } else {
                $courseid = $allcourse;
            }

            $sql = 'SELECT p.contents as contents, p.id as p_id, p.title as page, l.id as lesson_id, l.name as lesson_name,
                           c.id as subject_id, c.fullname as subject, cc.id as cat_id, cc.name as cat_name
                      FROM {lesson_pages} p
                INNER JOIN {lesson} l ON l.id = p.lessonid
                INNER JOIN {course} C ON c.id = l.course
                INNER JOIN {course_categories} cc ON cc.id = c.category
                     WHERE c.id IN ( '.$courseid.' ) AND ( contents LIKE "%.flv%" OR contents LIKE "%.swf%" OR contents LIKE
                           "%.mp4%" )';
            $values     = $DB->get_records_sql($sql);
            $arr        = array();
            $datareturn = array();

            foreach ($values as $ab) {
                $data = $ab->contents;
                $expl = explode('"', $data);
                $ar   = array();
                $ar1  = array();
                $ar2  = array();

                foreach ($expl as $val) {
                    $ar[] = explode('@@PLUGINFILE@@/', $val);
                    for ($i = 1; $i <= count($ar); $i = $i + 2) {
                        $ar1[] = $ar[$i][1];
                    }
                }

                $ar2 = array_unique($ar1);
                for ($i = 0; $i <= max(array_keys($ar2)); $i++) {
                    $check = stripos($ar2[$i], '.FLV');
                    $check2 = stripos($ar2[$i], '.SWF');
                    $check3 = stripos($ar2[$i], '.MP4');
                    if ($check OR $check2 OR $check3) {
                        $ar3['video_name'] = $ar2[$i];
                        $flash             = str_replace('%20', ' ', $ar3['video_name']);
                        $component         = 'mod_lesson';
                        $sqla = "SELECT f.contextid as contex, f.filearea as fname, f.itemid as item,
                                        f.timecreated as ctime, f.timemodified as mtime
                                   FROM {files} f
                             INNER JOIN {context} c ON f.contextid = c.id
                                  WHERE f.filename  LIKE :flash AND f.component = :component";

                        $record = $DB->get_recordset_sql($sqla, array('flash' => "%$flash%", 'component' => $component));

                        foreach ($record as $rec) {
                            $contextid    = $rec->contex;
                            $filearea     = $rec->fname;
                            $itemid       = $rec->item;
                            $createdtime  = date('d-m-Y, h:i:s A', $rec->ctime);
                            $modifiedtime = date('d-m-Y, h:i:s A', $rec->mtime);
                        }
                        $url = $CFG->wwwroot.
                        "/webservice/pluginfile.php/$contextid/$component/$filearea/$itemid/$flash?token=".FILES_TOKEN;
                    }
                }

                $datareturn[] = array('flash_file' => $url,
                                      'lesson_page_id' => $ab->p_id,
                                      'lesson_page_name' => $ab->page,
                                      'lesson_id' => $ab->lesson_id,
                                      'lesson_name' => $ab->lesson_name,
                                      'subject_id' => $ab->subject_id,
                                      'subject_name' => $ab->subject,
                                      'standard_id' => $ab->cat_id,
                                      'standard_name' => $ab->cat_name,
                                      'creation_datetime' => $createdtime,
                                      'updation_datetime' => $modifiedtime);
            }
            return $datareturn;
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a;
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function flash_videos_returns() {
        return new external_multiple_structure(
        new external_single_structure(
            array('flash_file' => new external_value(PARAM_RAW, 'flash url', VALUE_OPTIONAL),
                  'lesson_page_id' => new external_value(PARAM_RAW, 'lesson page id', VALUE_OPTIONAL),
                  'lesson_page_name' => new external_value(PARAM_RAW, 'lesson page name', VALUE_OPTIONAL),
                  'lesson_id' => new external_value(PARAM_RAW, 'Lesson id', VALUE_OPTIONAL),
                  'lesson_name' => new external_value(PARAM_RAW, 'Lesson name', VALUE_OPTIONAL),
                  'subject_id' => new external_value(PARAM_RAW, 'subject id', VALUE_OPTIONAL),
                  'subject_name' => new external_value(PARAM_RAW, 'subject name', VALUE_OPTIONAL),
                  'standard_id' => new external_value(PARAM_RAW, 'category id', VALUE_OPTIONAL),
                  'standard_name' => new external_value(PARAM_RAW, 'category name', VALUE_OPTIONAL),
                  'creation_datetime' => new external_value(PARAM_RAW, 'createdtime', VALUE_OPTIONAL),
                  'updation_datetime' => new external_value(PARAM_RAW, 'modifiedtime', VALUE_OPTIONAL),
                  'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL)
                 ), 'course'
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return flash_weekly_parameters
     */
    public static function flash_weekly_parameters() {
        return new external_function_parameters(
            array('username' => new external_value(PARAM_RAW, 'username'),
                  'password' => new external_value(PARAM_RAW, 'password'),
                  'startdate' => new external_value(PARAM_RAW, 'startdate'),
                  'enddate' => new external_value(PARAM_RAW, 'enddate'),
                  'cid' => new external_value(PARAM_INT, 'course id', VALUE_OPTIONAL)
                 )
        );
    }

    /**
     * Get total flash file url
     *
     * @param string $username access username
     * @param string $password access password
     * @param string $startdate startdate (dd-mm-yyyy)
     * @param string $enddate enddate (dd-mm-yyyy)
     * @param int $cid courseid
     * @return array
     */
    public static function flash_weekly($username, $password, $startdate, $enddate, $cid) {
        $params = self::validate_parameters(self::flash_weekly_parameters(),
            array('username' => $username,
                  'password' => $password,
                  'startdate' => $startdate,
                  'enddate' => $enddate,
                  'cid' => $cid));

        global $DB, $CFG;
        $sdate = $params[startdate];
        $sdate = explode('-', $sdate);
        $sdate = mktime(0, 0, 0, $sdate[1], $sdate[0], $sdate[2]);
        $edate = $params[enddate];
        $edate = explode('-', $edate);
        $edate = mktime(0, 0, 0, $edate[1], $edate[0], $edate[2]);
        $edate += 86400;
        $cid   = $params[cid];

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);

        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            $sqlab  = "SELECT count(id) as id FROM {course}";
            $counts = $DB->get_record_sql($sqlab);
            $a      = array();

            foreach (range(1, $counts->id) as $n) {
                $a[] = $n;
            }
            $allcourse = implode(',', $a);

            if ($params[cid]) {
                $courseid = $params[cid];
            } else {
                $courseid = $allcourse;
            }

            $sql = 'SELECT p.contents as contents, p.id as p_id, p.title as page, l.id as lesson_id, l.name as lesson_name,
                          c.id as subject_id, c.fullname as subject, cc.id as cat_id, cc.name as cat_name
                     FROM {lesson_pages} p
               INNER JOIN {lesson} l ON l.id = p.lessonid
               INNER JOIN {course} c ON c.id = l.course
               INNER JOIN {course_categories} cc ON cc.id = c.category
                    WHERE c.id IN ( '.$courseid.' ) AND (contents LIKE "%.flv%" OR contents LIKE "%.swf%"
                          OR contents LIKE "%.mp4%")';
            $values = $DB->get_recordset_sql($sql);
            $arr        = array();
            $datareturn = array();

            foreach ($values as $ab) {
                $data = $ab->contents;
                $expl = explode('"', $data);
                $ar   = array();
                $ar1  = array();
                $ar2  = array();

                foreach ($expl as $val) {
                    $ar[] = explode('@@PLUGINFILE@@/', $val);
                    for ($i = 1; $i <= count($ar); $i = $i + 2) {
                        $ar1[] = $ar[$i][1];
                    }
                }

                $ar2 = array_unique($ar1);

                for ($i = 0; $i <= max(array_keys($ar2)); $i++) {
                    $check = stripos($ar2[$i], '.FLV');
                    $check2 = stripos($ar2[$i], '.SWF');
                    $check3 = stripos($ar2[$i], '.MP4');

                    if ($check OR $check2 OR $check3) {
                        $ar3['video_name'] = $ar2[$i];
                        $flash = str_replace('%20', ' ', $ar3['video_name']);
                        $component = 'mod_lesson';
                        $sqla = 'SELECT f.id as fid, f.contextid as contex, f.filearea as fname, f.itemid as item,
                                        f.timecreated as ctime, f.timemodified as mtime
                                   FROM {files} f
                             INNER JOIN {context} c ON f.contextid = c.id
                                  WHERE f.filename LIKE "%'.$flash.'%"
                                        AND f.component = "'.$component.'"
                                        AND ((f.timemodified >= "'.$sdate.'" AND f.timemodified <= "'.$edate.'")
                                        OR (f.timecreated >= "'.$sdate.'" AND f.timecreated <= "'.$edate.'") )';
                        $record       = $DB->get_record_sql($sqla);
                        $contextid    = $record->contex;
                        $filearea     = $record->fname;
                        $itemid       = $record->item;
                        $flashid      = $record->fid;
                        $createdtime  = $record->ctime;
                        $modifiedtime = $record->mtime;

                        $url = $CFG->wwwroot.
                        "/webservice/pluginfile.php/$contextid/$component/$filearea/$itemid/$flash?token=".FILES_TOKEN;
                    }
                }

                $datareturn[] = array('flash_id' => $flashid,
                                      'flash_file' => $url,
                                      'lesson_page_id' => $ab->p_id,
                                      'lesson_page_name' => $ab->page,
                                      'lesson_id' => $ab->lesson_id,
                                      'lesson_name' => $ab->lesson_name,
                                      'subject_id' => $ab->subject_id,
                                      'subject_name' => $ab->subject,
                                      'standard_id' => $ab->cat_id,
                                      'standard_name' => $ab->cat_name,
                                      'createtime' => $createdtime,
                                      'modifiedtime' => $modifiedtime
                                      );
            }

            $return = array();
            foreach ($datareturn as $val) {
                if ( isset($val['modifiedtime']) OR isset($val['createtime']) ) {
                    $return[] = $val;
                }
            }

            $newrecord = array();
            foreach ($return as $val) {
                if ( $val['createtime'] >= $sdate AND $val['createtime'] <= $edate ) {
                    $recordstatus = 'New';
                } else {
                    $recordstatus = 'Updated';
                }

                $newrecord[] = array('flash_id' => $val['flash_id'],
                                     'flash_file' => $val['flash_file'],
                                     'lesson_page_id' => $val['lesson_page_id'],
                                     'lesson_page_name' => $val['lesson_page_name'],
                                     'lesson_id' => $val['lesson_id'],
                                     'lesson_name' => $val['lesson_name'],
                                     'subject_id' => $val['subject_id'],
                                     'subject_name' => $val['subject_name'],
                                     'standard_id' => $val['standard_id'],
                                     'standard_name' => $val['standard_name'],
                                     'creation_datetime' => date('d-m-Y, h:i:s A', $val['createtime']),
                                     'updation_datetime' => date('d-m-Y, h:i:s A', $val['modifiedtime']),
                                     'record_status' => $recordstatus,
                                    );
            }

            return $newrecord;
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a;
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function flash_weekly_returns() {
        return new external_multiple_structure(
        new external_single_structure(
            array('flash_id' => new external_value(PARAM_RAW, 'flash id', VALUE_OPTIONAL),
                  'flash_file' => new external_value(PARAM_RAW, 'flash url', VALUE_OPTIONAL),
                  'lesson_page_id' => new external_value(PARAM_RAW, 'lesson page id', VALUE_OPTIONAL),
                  'lesson_page_name' => new external_value(PARAM_RAW, 'lesson page name', VALUE_OPTIONAL),
                  'lesson_id' => new external_value(PARAM_RAW, 'lesson id', VALUE_OPTIONAL),
                  'lesson_name' => new external_value(PARAM_RAW, 'Lesson name', VALUE_OPTIONAL),
                  'subject_id' => new external_value(PARAM_RAW, 'course id', VALUE_OPTIONAL),
                  'subject_name' => new external_value(PARAM_RAW, 'course name', VALUE_OPTIONAL),
                  'standard_id' => new external_value(PARAM_RAW, 'category id', VALUE_OPTIONAL),
                  'standard_name' => new external_value(PARAM_RAW, 'category name', VALUE_OPTIONAL),
                  'creation_datetime' => new external_value(PARAM_RAW, 'createtime', VALUE_OPTIONAL),
                  'updation_datetime' => new external_value(PARAM_RAW, 'modifiedtmie', VALUE_OPTIONAL),
                  'record_status' => new external_value(PARAM_RAW, 'recordstatus', VALUE_OPTIONAL),
                  'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL)
                 ), 'course'
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return flash_batch_parameters
     */
    public static function flash_batch_parameters() {
        return new external_function_parameters(
            array('username' => new external_value(PARAM_RAW, 'human description of PARAM1'),
                  'password' => new external_value(PARAM_RAW, 'human description of PARAM2'),
                  'no_of_record' => new external_value(PARAM_INT, 'human description of PARAM3'),
                  'start_point' => new external_value(PARAM_INT, 'human description of PARAM4', VALUE_OPTIONAL)
                 )
        );
    }

    /**
     * Get total flash file url
     *
     * @param string $username access username
     * @param string $password access password
     * @param int $noofrecord no. of row
     * @param string $startpoint start value
     * @return array
     */
    public static function flash_batch($username, $password, $noofrecord, $startpoint) {
        $params = self::validate_parameters(self::flash_batch_parameters(),
                      array('username' => $username,
                            'password' => $password,
                            'no_of_record' => $noofrecord,
                            'start_point' => $startpoint));

        global $DB, $CFG, $USER;
        $noofquestions = $params[no_of_record];
        $startpoints = $params[start_point];

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);
        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            $sqlab = "SELECT count(id) as id FROM {course}";
            $counts = $DB->get_record_sql($sqlab);
            $a = array();
            foreach (range(1, $counts->id) as $n) {
                $a[] = $n;
            }
            $allcourse = implode(',', $a);
            if ($params[cid]) {
                $courseid = $params[cid];
            } else {
                $courseid = $allcourse;
            }
            if ($params[no_of_record]) {
                $noofquestions = $params[no_of_record];
            } else {
                $noofquestions = 2;
            }

            $sqlab = 'SELECT count(p.contents) as qcount
                        FROM {lesson_pages} p
                  INNER JOIN {lesson} l ON l.id = p.lessonid
                  INNER JOIN {course} c ON c.id = l.course
                  INNER JOIN {course_categories} cc ON cc.id = c.category
                       WHERE c.id IN ( '.$courseid.' ) AND (contents LIKE "%.flv%" OR contents LIKE "%.swf%"
                             OR contents LIKE "%.mp4%")';
            $qcount = $DB->get_record_sql( $sqlab );
            $sqlac = "SELECT id, batch, noofquestion
                        FROM {lessonquizapi_batchdetails}
                       WHERE functionname = :gflashfile ORDER BY id DESC LIMIT 0,1";
            $batch = $DB->get_record_sql($sqlac, array('gflashfile' => 'get_flash_file'));
            $record = new stdClass();
            if (empty($batch)) {
                $batchno = 0;
                $offset  = 0;
            } else {
                if ($batch->batch == 0) {
                    $batchno = $batch->noofquestion;
                    $offset  = $batch->noofquestion;
                } else {
                    if (($batch->batch + $noofquestions) < $qcount->qcount) {
                        $offset  = ($batch->batch + $batch->noofquestion);
                        $batchno = $offset;
                    } else {
                        $offset  = ($batch->batch);
                        $batchno = $offset + 1;
                    }
                }
            }
            $record->batch = $batchno;
            if (isset($startpoints)) {
                $limit = " LIMIT ".$startpoints.",".$noofquestions;
            } else {
                if (($batch->batch + $batch->noofquestion) == $qcount->qcount) {
                    $limit = " LIMIT ".$qcount->qcount.",".$noofquestions;
                } else {
                    $limit = " LIMIT ".$offset.",".$noofquestions;
                }
            }

            $sql = 'SELECT p.contents as contents, p.id as p_id, p.title as page, l.id as lesson_id, l.name as lesson_name,
                           c.id as subject_id, c.fullname as subject, cc.id as cat_id, cc.name as cat_name
                      FROM {lesson_pages} p
                INNER JOIN {lesson} l ON l.id = p.lessonid
                INNER JOIN {course} c ON c.id = l.course
                INNER JOIN {course_categories} cc ON cc.id = c.category
                     WHERE c.id IN ( '.$courseid.' ) AND (contents LIKE "%.flv%" OR contents LIKE "%.swf%"
                           OR contents LIKE "%.mp4%") '.$limit;

            $questionsvalues = $DB->get_records_sql($sql);
            if (empty($questionsvalues)) {
                $message = 'Records Not Available!';
            } else {
                if (!isset($startpoints)) {
                    if ($batchno < $qcount->qcount) {
                        if ($batchno + $noofquestions < $qcount->qcount) {
                            $record->userid       = $USER->id;
                            $record->noofquestion = $noofquestions;
                            $record->functionname = 'get_flash_file';
                            $record->timecreated  = time();
                            $record->timemodified = time();
                            $DB->insert_record('lessonquizapi_batchdetails', $record);
                            $message = 'More Records Available!';
                        } else {
                            $record->userid       = $USER->id;
                            $record->noofquestion = count($questionsvalues);
                            $record->functionname = 'get_flash_file';
                            $record->timecreated  = time();
                            $record->timemodified = time();
                            $DB->insert_record('lessonquizapi_batchdetails', $record);
                            $message = ' No More Records Available!';
                        }
                    }
                }
            }

            $values = $DB->get_recordset_sql($sql, array('courseid' => $courseid, 'flv' => "%.flv%", 'swf' => "%.swf%",
                          'mp4' => "%.mp4%"));
            $arr        = array();
            $datareturn = array();

            foreach ($values as $ab) {
                $data = $ab->contents;
                $expl = explode('"', $data);
                $ar   = array();
                $ar1  = array();
                $ar2  = array();

                foreach ($expl as $val) {
                    $ar[] = explode('@@PLUGINFILE@@/', $val);

                    for ($i = 1; $i <= count($ar); $i = $i + 2) {
                        $ar1[] = $ar[$i][1];
                    }
                }

                $ar2 = array_unique($ar1);
                for ($i = 0; $i <= max(array_keys($ar2)); $i++) {
                    $check  = stripos($ar2[$i], '.FLV');
                    $check2 = stripos($ar2[$i], '.SWF');
                    $check3 = stripos($ar2[$i], '.MP4');

                    if ($check OR $check2 OR $check3) {
                        $ar3['video_name'] = $ar2[$i];
                        $flash = str_replace('%20', ' ', $ar3['video_name']);
                        $component = 'mod_lesson';
                        $sqla = "SELECT f.id as fid, f.contextid as contex, f.filearea as fname, f.itemid as item,
                                        f.timecreated as ctime, f.timemodified as mtime
                                   FROM {files} f
                             INNER JOIN {context} c ON f.contextid = c.id
                                  WHERE f.filename  LIKE :flash AND f.component = :component";
                        $record = $DB->get_recordset_sql($sqla, array('flash' => "%$flash%", 'component' => $component));

                        foreach ($record as $rec) {
                            $contextid    = $rec->contex;
                            $filearea     = $rec->fname;
                            $itemid       = $rec->item;
                            $flashid      = $rec->fid;
                            $createdtime  = date('d-m-Y, h:i:s A', $rec->ctime);
                            $modifiedtime = date('d-m-Y, h:i:s A', $rec->mtime);
                        }
                        $url = $CFG->wwwroot.
                        "/webservice/pluginfile.php/$contextid/$component/$filearea/$itemid/$flash?token=".FILES_TOKEN;
                    }
                }

                $datareturn[] = array('flash_id' => $flashid,
                                      'flash_file' => $url,
                                      'lesson_page_id' => $ab->p_id,
                                      'lesson_page_name' => $ab->page,
                                      'lesson_id' => $ab->lesson_id,
                                      'lesson_name' => $ab->lesson_name,
                                      'subject_id' => $ab->subject_id,
                                      'subject_name' => $ab->subject,
                                      'standard_id' => $ab->cat_id,
                                      'standard_name' => $ab->cat_name,
                                      'creation_datetime' => $createdtime,
                                      'updation_datetime' => $modifiedtime,
                                      'message' => $message,
                                      'total_record' => $qcount->qcount);
            }

            return $datareturn;
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a;
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function flash_batch_returns() {
        return new external_multiple_structure(
        new external_single_structure(
            array('flash_id' => new external_value(PARAM_RAW, 'flash id', VALUE_OPTIONAL),
                  'flash_file' => new external_value(PARAM_RAW, 'flash url', VALUE_OPTIONAL),
                  'lesson_page_id' => new external_value(PARAM_RAW, 'page name of that flash file', VALUE_OPTIONAL),
                  'lesson_page_name' => new external_value(PARAM_RAW, 'page name of that flash file', VALUE_OPTIONAL),
                  'lesson_id' => new external_value(PARAM_RAW, 'Lesson id of the flash file', VALUE_OPTIONAL),
                  'lesson_name' => new external_value(PARAM_RAW, 'Lesson name of the flash file', VALUE_OPTIONAL),
                  'subject_id' => new external_value(PARAM_RAW, 'subject name of the flash file', VALUE_OPTIONAL),
                  'subject_name' => new external_value(PARAM_RAW, 'subject name of the flash file', VALUE_OPTIONAL),
                  'standard_id' => new external_value(PARAM_RAW, 'subject name of the flash file', VALUE_OPTIONAL),
                  'standard_name' => new external_value(PARAM_RAW, 'subject name of the flash file', VALUE_OPTIONAL),
                  'creation_datetime' => new external_value(PARAM_RAW, 'timecreated of the flash file', VALUE_OPTIONAL),
                  'updation_datetime' => new external_value(PARAM_RAW, 'timecreated of the flash file', VALUE_OPTIONAL),
                  'message' => new external_value(PARAM_RAW, 'Erro', VALUE_OPTIONAL),
                  'total_record' => new external_value(PARAM_RAW, 'Erro', VALUE_OPTIONAL)
                  ), 'course'
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return get_lessonquestions_parameters
     */
    public static function get_lessonquestions_parameters() {
        return new external_function_parameters(
            array('username' => new external_value(PARAM_RAW, 'username'),
                  'password' => new external_value(PARAM_RAW, 'password'),
                  'lessonid' => new external_value(PARAM_INT, 'human description of PARAM1'),
                  'startdate' => new external_value(PARAM_RAW, 'start date', VALUE_OPTIONAL),
                  'enddate' => new external_value(PARAM_RAW, 'end date', VALUE_OPTIONAL)
                  )
        );
    }

    /**
     * Get lesson records
     *
     * @param string $username access username
     * @param string $password access password
     * @param int $lessonid lesson id
     * @param string $startdate startdate (dd-mm-yyyy) optional value
     * @param string $enddate enddate (dd-mm-yyyy) optional value
     * @return array
     */
    public static function get_lessonquestions($username, $password, $lessonid, $startdate = null, $enddate = null) {
        global $CFG, $DB;
        $params = array('username' => $username,
                        'password' => $password,
                        'lessonid' => $lessonid,
                        'startdate' => $startdate,
                        'enddate' => $enddate);

        $params = self::validate_parameters(self::get_lessonquestions_parameters(), $params);
        $stdate = $params['startdate'];
        $edate  = $params['enddate'];
        $stdate = explode('-', $stdate);
        $stdate = mktime(0, 0, 0, $stdate[1], $stdate[0], $stdate[2]);
        $edate  = explode('-', $edate);
        $edate  = mktime(0, 0, 0, $edate[1], $edate[0], $edate[2]);
        $edate  += 86400;

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);

        $ary = array();
        foreach ($authdata as $val) {
            $ary['true']  = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            if (!empty($params['startdate']) && !empty($params['enddate'])) {
                $sql = "SELECT COUNT(id) AS qcount
                          FROM {lesson_pages}
                         WHERE lessonid = :lessonid
                               AND IF (timemodified = 0, timecreated, timemodified) >= :stdate
                               AND IF(timemodified = 0, timecreated, timemodified) < :edate";

                $qcount = $DB->get_record_sql($sql, array('lessonid' => $params['lessonid'], 'stdate' => $stdate,
                                                    'edate' => $edate));

                $sql = "SELECT *, IF (timemodified = 0, 'New', 'Updated') AS recordstatus  FROM {lesson_pages}
                         WHERE lessonid = :lessonid
                               AND IF (timemodified = 0, timecreated, timemodified) >= :stdate
                               AND IF(timemodified = 0, timecreated, timemodified) < :edate";
            } else {
                $sql = "SELECT COUNT(id) AS qcount FROM {lesson_pages}
                         WHERE lessonid = :lessonid";

                $qcount = $DB->get_record_sql($sql, array('lessonid' => $params['lessonid']));
                $sql = "SELECT *, IF (timemodified = 0, 'New', 'Updated') AS recordstatus FROM {lesson_pages}
                         WHERE lessonid = :lessonid";
            }

            $questionsvalues = $DB->get_records_sql($sql, array('lessonid' => $params['lessonid'], 'stdate' => $stdate,
                                                          'edate' => $edate));
            if (!empty($questionsvalues)) {
                $returnvalue = array();
                foreach ($questionsvalues as $value) {
                    $questionval                      = array();
                    $questionval['id']                = $value->id;
                    $questionval['lesson_id']         = $params['lessonid'];
                    $questionval['creation_datetime'] = date('d-m-Y, h:i:s A', $value->timecreated);
                    $questionval['updation_datetime'] = ($value->timemodified == 0) ? 0 : date('d-m-Y, h:i:s A',
                                                         $value->timemodified);
                    $questionval['record_status']     = $value->recordstatus;
                    $questionval['title']             = $value->title;

                    $newcontents = lessonimages($value->contents);
                    $questionval['questiontext'] = $newcontents;
                    $returnvalue[] = $questionval;
                }
                $returnvalue1['lquestiondetails'] = $returnvalue;
                $returnvalue1['total_record'] = $qcount->qcount;
                return $returnvalue1;
            } else {
                 $a   = array();
                 $a[] = array('message' => 'Records not Available or Lesson id does not exists!');
                 return $a[0];
            }
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a[0];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_lessonquestions_returns() {
        return new external_function_parameters(
            array(
                'lquestiondetails' => new external_multiple_structure(
                new external_single_structure(
                    array('id' => new external_value(PARAM_INT, 'course id', VALUE_OPTIONAL),
                          'lesson_id' => new external_value(PARAM_INT, 'lesson id', VALUE_OPTIONAL),
                          'creation_datetime' => new external_value(PARAM_RAW, 'course short name', VALUE_OPTIONAL),
                          'updation_datetime' => new external_value(PARAM_RAW, 'category id', VALUE_OPTIONAL),
                          'record_status' => new external_value(PARAM_TEXT, 'full name', VALUE_OPTIONAL),
                          'title' => new external_value(PARAM_TEXT, 'recordstatus', VALUE_OPTIONAL),
                          'questiontext' => new external_value(PARAM_RAW, 'summary', VALUE_OPTIONAL),
                          )
                ), 'Users', VALUE_OPTIONAL
                ),
            'total_record' => new external_value(PARAM_INT, 'quiz id', VALUE_OPTIONAL),
            'message' => new external_value(PARAM_RAW, 'summary', VALUE_OPTIONAL),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return get_lessquestion_parameters
     */
    public static function get_lessquestion_parameters() {
        return new external_function_parameters(
            array('username' => new external_value(PARAM_RAW, 'username'),
                  'password' => new external_value(PARAM_RAW, 'password'),
                  'lessonid' => new external_value(PARAM_INT, 'human description of PARAM1'),
                  'startdate' => new external_value(PARAM_RAW, 'start date', VALUE_OPTIONAL),
                  'enddate' => new external_value(PARAM_RAW, 'end date', VALUE_OPTIONAL)
                  )
        );
    }

    /**
     * Get lesson question, answers
     *
     * @param string $username access username
     * @param string $password access password
     * @param int $lessonid lesson id
     * @param string $startdate startdate (dd-mm-yyyy) optional value
     * @param string $enddate enddate (dd-mm-yyyy) optional value
     * @return array
     */
    public static function get_lessquestion($username, $password, $lessonid, $startdate, $enddate) {
        global $CFG, $DB;
        $params = array('username' => $username,
                        'password' => $password,
                        'lessonid' => $lessonid,
                        'startdate' => $startdate,
                        'enddate' => $enddate);

        $params = self::validate_parameters(self::get_lessquestion_parameters(), $params);
        $stdate = $params['startdate'];
        $edate  = $params['enddate'];
        $stdate = explode('-', $stdate);
        $stdate = mktime(0, 0, 0, $stdate[1], $stdate[0], $stdate[2]);
        $edate  = explode('-', $edate);
        $edate  = mktime(0, 0, 0, $edate[1], $edate[0], $edate[2]);
        $edate  += 86400;

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);
        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            if (!empty($params['startdate']) && !empty($params['enddate'])) {
                $sql = "SELECT COUNT(lp.id) AS qcount
                          FROM {lesson_pages} lp
                          JOIN {lesson} l ON l.id = lp.lessonid
                          JOIN {course} c ON c.id = l.course
                          JOIN {course_categories} cc ON cc.id = c.category
                         WHERE lp.lessonid = :lessonid AND lp.qtype IN (1,2,3,5,8,10)
                               AND IF (lp.timemodified = 0, lp.timecreated, lp.timemodified) >= :stdate
                               AND IF(lp.timemodified = 0, lp.timecreated, lp.timemodified) < :edate";

                $qcount = $DB->get_record_sql($sql, array('lessonid' => $params['lessonid'], 'stdate' => $stdate,
                                                          'edate' => $edate));

                $sql = "SELECT lp.id, lp.title, lp.contents, lp.qtype, lp.timecreated, lp.timemodified, c.fullname,
                               c.id AS courseid, l.id AS lessonid, cc.id AS categoryid, cc.name AS categoryname,
                               l.name AS lessonname, IF (lp.timemodified = 0, 'New', 'Updated') AS recordstatus
                          FROM {lesson_pages} lp
                          JOIN {lesson} l ON l.id = lp.lessonid
                          JOIN {course} c ON c.id = l.course
                          JOIN {course_categories} cc ON cc.id = c.category
                         WHERE lp.lessonid = :lessonid AND lp.qtype IN (1,2,3,5,8,10)
                               AND IF (lp.timemodified = 0, lp.timecreated, lp.timemodified) >= :stdate
                               AND IF(lp.timemodified = 0, lp.timecreated, lp.timemodified) < :edate";
            } else {
                $sql = "SELECT COUNT(lp.id) AS qcount
                          FROM {lesson_pages} lp
                          JOIN {lesson} l ON l.id = lp.lessonid
                          JOIN {course} c ON c.id = l.course
                          JOIN {course_categories} cc ON cc.id = c.category
                         WHERE lp.lessonid = :lessonid AND lp.qtype IN (1,2,3,5,8,10)";

                $qcount = $DB->get_record_sql($sql, array('lessonid' => $params['lessonid']));

                $sql = "SELECT lp.id, lp.title, lp.contents, lp.qtype, lp.timecreated, lp.timemodified, c.fullname,
                               c.id AS courseid, l.id AS lessonid, cc.id AS categoryid, cc.name AS categoryname,
                               l.name AS lessonname, IF (lp.timemodified = 0, 'New', 'Updated') AS recordstatus
                          FROM {lesson_pages} lp
                          JOIN {lesson} l ON l.id = lp.lessonid
                          JOIN {course} c ON c.id = l.course
                          JOIN {course_categories} cc ON cc.id = c.category
                         WHERE lp.lessonid = :lessonid AND lp.qtype IN (1,2,3,5,8,10)";
            }

            $questionsvalues = $DB->get_records_sql($sql, array('lessonid' => $params['lessonid'], 'stdate' => $stdate,
                                                          'edate' => $edate));

            $returnvalue = array();
            if (!empty($questionsvalues)) {
                foreach ($questionsvalues as $value) {
                    $sqlad = "SELECT id, answer, score
                                FROM {lesson_answers}
                               WHERE pageid = :pageid";
                    $answeroptions = $DB->get_records_sql($sqlad, array('pageid' => $value->id));

                    foreach ($answeroptions as $val) {
                        $answers           = array();
                        $answers['value1'] = $val->answer;
                        $answers['score']  = number_format((float)$val->score, 2, '.', '');
                        $var1[]            = $answers;
                    }

                    $questionval                      = array();
                    $questionval['lpid']              = $value->id;
                    $questionval['standard_id']       = $value->categoryid;
                    $questionval['standard_name']     = $value->categoryname;
                    $questionval['subject_id']        = $value->courseid;
                    $questionval['subject_name']      = $value->fullname;
                    $questionval['lesson_id']         = $value->lessonid;
                    $questionval['lesson_name']       = $value->lessonname;
                    $questionval['answer']            = $var1;
                    $questionval['title']             = $value->title;
                    $questionval['creation_datetime'] = date('d-m-Y, h:i:s A', $value->timecreated);
                    $questionval['updation_datetime'] = ($value->timemodified == 0) ? 0 : date('d-m-Y, h:i:s A',
                                                         $value->timemodified);
                    $questionval['record_status']     = $value->recordstatus;
                    $questionval['questiontext']          = lessonimages($value->contents);

                    if ($value->qtype == 1) {
                        $questionval['questiontype'] = 'Short Answer';
                    } else if ($value->qtype == 2) {
                        $questionval['questiontype'] = 'True/false';
                    } else if ($value->qtype == 3) {
                        $questionval['questiontype'] = 'Multichoice';
                    } else if ($value->qtype == 5) {
                        $questionval['questiontype'] = 'Matching';
                    } else if ($value->qtype == 8) {
                        $questionval['questiontype'] = 'Numerical';
                    } else if ($value->qtype == 10) {
                        $questionval['questiontype'] = 'Essay';
                    } else if ($value->qtype == 20) {
                        $questionval['questiontype'] = 'Page Contents';
                    } else {
                        $questionval['questiontype'] = 'Lesson Type question not matched.';
                    }
                    unset($answers);
                    unset($var1);
                    $returnvalue[] = $questionval;
                }
                $returnvalue1['lquestiondetails'] = $returnvalue;
                $returnvalue1['total_record'] = $qcount->qcount;

                return $returnvalue1;
            } else {
                $a   = array();
                $a[] = array('message' => 'Records not Available or Lesson id is not exists!');
                return $a[0];
            }
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a[0];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_lessquestion_returns() {
        return new external_function_parameters(
            array('lquestiondetails' => new external_multiple_structure(
                new external_single_structure(
                    array('lpid' => new external_value(PARAM_INT, 'lesson page id'),
                          'standard_id' => new external_value(PARAM_INT, 'category id'),
                          'standard_name' => new external_value(PARAM_RAW, 'category name'),
                          'subject_id' => new external_value(PARAM_INT, 'course id'),
                          'subject_name' => new external_value(PARAM_RAW, 'course name'),
                          'lesson_id' => new external_value(PARAM_INT, 'lesson id'),
                          'lesson_name' => new external_value(PARAM_RAW, 'lesson name'),
                          'answer' => new external_multiple_structure(
                              new external_single_structure(
                                  array('value1' => new external_value(PARAM_RAW, 'option'),
                                        'score' => new external_value(PARAM_RAW, 'answer'),
                                       )
                              ), 'answer', VALUE_OPTIONAL
                          ),
                          'title' => new external_value(PARAM_TEXT, 'lesson page title'),
                          'questiontext' => new external_value(PARAM_RAW, 'content'),
                          'creation_datetime' => new external_value(PARAM_RAW, 'createdtime'),
                          'updation_datetime' => new external_value(PARAM_RAW, 'modifiedtime'),
                          'record_status' => new external_value(PARAM_TEXT, 'recordstatus'),
                          'questiontype' => new external_value(PARAM_RAW, 'questiontype')
                          )
                ), 'lesson question details', VALUE_OPTIONAL
                ),
            'total_record' => new external_value(PARAM_INT, 'total no. of record', VALUE_OPTIONAL),
            'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return get_lessonquestionbyid_parameters
     */
    public static function get_lessonquestionbyid_parameters() {
        return new external_function_parameters(
            array('username' => new external_value(PARAM_RAW, 'username'),
                  'password' => new external_value(PARAM_RAW, 'password'),
                  'lessonquestionid' => new external_value(PARAM_INT, 'human description of PARAM1'),
                 )
        );
    }

    /**
     * Get lesson record by lesson question id
     *
     * @param string $username access username
     * @param string $password access password
     * @param int $lessonquestionid lesson question id
     * @return array
     */
    public static function get_lessonquestionbyid($username, $password, $lessonquestionid) {
        global $CFG, $DB;
        $params = array('username' => $username,
                        'password' => $password,
                        'lessonquestionid' => $lessonquestionid);

        $params = self::validate_parameters(self::get_lessonquestionbyid_parameters(), $params);

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);

        $ary = array();

        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            $sql = "SELECT lp.id, lp.title, lp.contents, lp.qtype, lp.timecreated, lp.timemodified, c.fullname,
                           c.id AS courseid, l.id AS lessonid, cc.id AS categoryid, cc.name AS categoryname,
                           l.name AS lessonname, IF (lp.timemodified = 0, 'New', 'Updated') AS recordstatus
                      FROM {lesson_pages} lp
                      JOIN {lesson} l ON l.id = lp.lessonid
                      JOIN {course} c ON c.id = l.course
                      JOIN {course_categories} cc ON cc.id = c.category
                     WHERE lp.id = :lessonquestionid AND lp.qtype IN (1,2,3,5,8,10)";
            $questionsvalue = $DB->get_record_sql($sql, array('lessonquestionid' => $params['lessonquestionid']));
            if (!empty($questionsvalue)) {
                $sqlae = "SELECT id, answer, score
                            FROM {lesson_answers}
                           WHERE pageid = :pageid";
                $answeroptions = $DB->get_records_sql($sqlae, array('pageid' => $questionsvalue->id));

                foreach ($answeroptions as $val) {
                    $answers = array();
                    $answers['value1'] = $val->answer;
                    $answers['score'] = number_format((float)$val->score, 2, '.', '');
                    $var1[] = $answers;
                }

                $questionval = array();
                $questionval['lpid'] = $questionsvalue->id;
                $questionval['standard_id'] = $questionsvalue->categoryid;
                $questionval['standard_name'] = $questionsvalue->categoryname;
                $questionval['subject_id'] = $questionsvalue->courseid;
                $questionval['subject_name'] = $questionsvalue->fullname;
                $questionval['lesson_id'] = $questionsvalue->lessonid;
                $questionval['lesson_name'] = $questionsvalue->lessonname;
                $questionval['answer'] = $var1;
                $questionval['title'] = $questionsvalue->title;
                $questionval['creation_datetime'] = date('d-m-Y, h:i:s A', $questionsvalue->timecreated);
                $questionval['updation_datetime'] = ($questionsvalue->timemodified == 0) ? 0 : date('d-m-Y, h:i:s A',
                                                     $questionsvalue->timemodified);
                $questionval['record_status'] = $questionsvalue->recordstatus;
                $questionval['questiontext'] = lessonimages($questionsvalue->contents);

                if ($questionsvalue->qtype == 1) {
                    $questionval['questiontype'] = 'Short Answer';
                } else if ($questionsvalue->qtype == 2) {
                    $questionval['questiontype'] = 'True/false';
                } else if ($questionsvalue->qtype == 3) {
                    $questionval['questiontype'] = 'Multichoice';
                } else if ($questionsvalue->qtype == 5) {
                    $questionval['questiontype'] = 'Matching';
                } else if ($questionsvalue->qtype == 8) {
                    $questionval['questiontype'] = 'Numerical';
                } else if ($questionsvalue->qtype == 10) {
                    $questionval['questiontype'] = 'Essay';
                } else {
                    $questionval['questiontype'] = 'Lesson Type question not matched.';
                }
                unset($answers);
                unset($var1);
                $returnvalue[] = $questionval;

                return $returnvalue;
            } else {
                $a   = array();
                $a[] = array('message' => 'Lesson Question id does not exists!');
                return $a;
            }
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a;
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_lessonquestionbyid_returns() {
        return new external_multiple_structure(
        new external_single_structure(
            array('lpid' => new external_value(PARAM_INT, 'lesson page id', VALUE_OPTIONAL),
                  'standard_id' => new external_value(PARAM_INT, 'category id', VALUE_OPTIONAL),
                  'standard_name' => new external_value(PARAM_RAW, 'category name', VALUE_OPTIONAL),
                  'subject_id' => new external_value(PARAM_INT, 'course id', VALUE_OPTIONAL),
                  'subject_name' => new external_value(PARAM_RAW, 'course name', VALUE_OPTIONAL),
                  'lesson_id' => new external_value(PARAM_INT, 'lesson id', VALUE_OPTIONAL),
                  'lesson_name' => new external_value(PARAM_RAW, 'lesson name', VALUE_OPTIONAL),
                  'answer' => new external_multiple_structure(
                      new external_single_structure(
                          array('value1' => new external_value(PARAM_RAW, 'course short name', VALUE_OPTIONAL),
                                'score' => new external_value(PARAM_RAW, 'question score', VALUE_OPTIONAL)
                               )), 'answer', VALUE_OPTIONAL
                  ),
                  'title' => new external_value(PARAM_TEXT, 'lesson page title', VALUE_OPTIONAL),
                  'questiontext' => new external_value(PARAM_RAW, 'content', VALUE_OPTIONAL),
                  'creation_datetime' => new external_value(PARAM_RAW, 'createdtime', VALUE_OPTIONAL),
                  'updation_datetime' => new external_value(PARAM_RAW, 'modifiedtime', VALUE_OPTIONAL),
                  'record_status' => new external_value(PARAM_TEXT, 'recordstatus', VALUE_OPTIONAL),
                  'questiontype' => new external_value(PARAM_RAW, 'questiontype', VALUE_OPTIONAL),
                  'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL)
            ), 'lesson details'
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return get_courselessons_parameters
     */
    public static function get_courselessons_parameters() {
        return new external_function_parameters(
            array('username' => new external_value(PARAM_RAW, 'the username'),
                  'password' => new external_value(PARAM_RAW, 'the password'),
                  'no_of_record' => new external_value(PARAM_INT, 'no. of record'),
                  'start_point' => new external_value(PARAM_INT, 'start point default 0', VALUE_OPTIONAL, 0)
                 )
        );
    }

    /**
     * Get lesson record by lesson question id
     *
     * @param string $username access username
     * @param string $password access password
     * @param int $noofquestion fetch no. of row
     * @param int $startposition starting position
     * @return array
     */
    public static function get_courselessons($username, $password, $noofquestion, $startposition) {
        global $CFG, $DB, $USER;
        $params = array('username' => $username,
                        'password' => $password,
                        'no_of_record' => $noofquestion,
                        'start_point' => $startposition);

        $params = self::validate_parameters(self::get_courselessons_parameters(), $params);
        $noofquestions = $params['no_of_record'];
        $startposition = $params['start_point'];

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);

        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            $sql = "SELECT COUNT(lp.id) AS qcount
                      FROM {lesson_pages} lp
                      JOIN {lesson} l ON l.id = lp.lessonid
                      JOIN {course} c ON c.id = l.course
                      JOIN {course_categories} cc ON cc.id = c.category";
            $qcount = $DB->get_record_sql($sql);
            $sql = "SELECT id, batch, noofquestion
                      FROM {lessonquizapi_batchdetails}
                     WHERE functionname = :gcourselesson ORDER BY id DESC LIMIT 0,1";
            $batch = $DB->get_record_sql($sql, array('gcourselesson' => 'get_courselessons'));

            $record = new stdClass();
            if (isset($startposition)) {
                $limit = " LIMIT ".$startposition.",".$noofquestions;
            } else {
                if (empty($batch)) {
                    $limit = "";
                    $limit = " LIMIT 0,".$noofquestions;
                    $record->batch = 0;
                    $startpoint = 1;
                    $endpoint = $noofquestions;
                } else {
                    if ($batch->batch == 0) {
                        $batchno = $batch->noofquestion;
                        $startpoint = $batch->noofquestion + 1;
                        $endpoint = $batch->noofquestion + $noofquestions;
                    } else {
                        $batchno = ($batch->batch + $batch->noofquestion);
                        $startpoint = $batchno + 1;
                        $endpoint = $batchno + $noofquestions;
                    }
                    $limit = " LIMIT ".$batchno.",".$noofquestions;
                    $record->batch = $batchno;
                }
            }

            $sql = "SELECT lp.id, lp.title, lp.contents, lp.qtype, lp.timecreated, lp.timemodified, c.fullname,
                           c.id AS courseid, l.id AS lessonid, cc.id AS categoryid, cc.name AS categoryname,
                           l.name AS lessonname, IF (lp.timemodified = 0, 'New', 'Updated') AS recordstatus
                      FROM {lesson_pages} lp
                      JOIN {lesson} l ON l.id = lp.lessonid
                      JOIN {course} c ON c.id = l.course
                      JOIN {course_categories} cc ON cc.id = c.category".$limit;
            $questionsvalues = $DB->get_records_sql($sql);

            if (empty($questionsvalues)) {
                $message = 'Records Not Available!';
            } else if (isset($startposition)) {
                $message = 'Records Available!';
            } else {
                if ($batchno < $qcount->qcount) {
                    if ($batchno + $noofquestions < $qcount->qcount) {
                        $record->userid = $USER->id;
                        $record->noofquestion = $noofquestions;
                        $record->functionname = 'get_courselessons';
                        $record->timecreated = time();
                        $record->timemodified = time();
                        $DB->insert_record('lessonquizapi_batchdetails', $record);
                        $message = 'Records Available!';
                    } else {
                        $record->userid = $USER->id;
                        $record->noofquestion = count($questionsvalues);
                        $record->functionname = 'get_courselessons';
                        $record->timecreated = time();
                        $record->timemodified = time();
                        $DB->insert_record('lessonquizapi_batchdetails', $record);
                        $message = 'More Records Not Available!';
                        $endpoint = ($startpoint + count($questionsvalues) - 1);
                    }
                }
            }

            $returnvalue = array();
            foreach ($questionsvalues as $value) {
                $questionval = array();
                $questionval['lpid']              = $value->id;
                $questionval['standard_id']       = $value->categoryid;
                $questionval['standard_name']     = $value->categoryname;
                $questionval['subject_id']        = $value->courseid;
                $questionval['subject_name']      = $value->fullname;
                $questionval['lesson_id']         = $value->lessonid;
                $questionval['lesson_name']       = $value->lessonname;
                $questionval['title']             = $value->title;
                $questionval['questiontext']      = lessonimages($value->contents);
                $questionval['creation_datetime'] = date('d-m-Y, h:i:s A', $value->timecreated);
                $questionval['updation_datetime'] = ($value->timemodified == 0) ? 0 : date('d-m-Y, h:i:s A',
                                                     $value->timemodified);
                $questionval['record_status']     = $value->recordstatus;

                if ($value->qtype == 1) {
                    $questionval['questiontype'] = 'Short Answer';
                } else if ($value->qtype == 2) {
                    $questionval['questiontype'] = 'True/false';
                } else if ($value->qtype == 3) {
                    $questionval['questiontype'] = 'Multichoice';
                } else if ($value->qtype == 5) {
                    $questionval['questiontype'] = 'Matching';
                } else if ($value->qtype == 8) {
                    $questionval['questiontype'] = 'Numerical';
                } else if ($value->qtype == 10) {
                    $questionval['questiontype'] = 'Essay';
                } else if ($value->qtype == 20) {
                    $questionval['questiontype'] = 'Page Contents';
                } else {
                    echo $value->qtype.'<br/>';
                    $questionval['questiontype'] = 'Lesson Type question not matched.';
                }
                unset($answers);
                unset($var1);
                $returnvalue[] = $questionval;
            }
            $returnvalue1['lquestiondetails'] = $returnvalue;
            $returnvalue1['message'] = $message;
            if (isset($startposition)) {
                $returnvalue1['start_point'] = $startposition;
            } else {
                $returnvalue1['start_point'] = $startpoint;
            }
            $returnvalue1['no_of_record'] = $noofquestions;
            $returnvalue1['total_record'] = $qcount->qcount;

            return $returnvalue1;
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a[0];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_courselessons_returns() {
        return new external_function_parameters(
            array('lquestiondetails' => new external_multiple_structure(
                new external_single_structure(
                    array('lpid' => new external_value(PARAM_INT, 'lesson page id'),
                          'standard_id' => new external_value(PARAM_INT, 'category id'),
                          'standard_name' => new external_value(PARAM_RAW, 'category name'),
                          'subject_id' => new external_value(PARAM_INT, 'course id'),
                          'subject_name' => new external_value(PARAM_RAW, 'course name'),
                          'lesson_id' => new external_value(PARAM_INT, 'lesson id'),
                          'lesson_name' => new external_value(PARAM_RAW, 'lesson name'),
                          'title' => new external_value(PARAM_TEXT, 'lesson page title'),
                          'questiontext' => new external_value(PARAM_RAW, 'content'),
                          'creation_datetime' => new external_value(PARAM_RAW, 'createdtime'),
                          'updation_datetime' => new external_value(PARAM_RAW, 'modifiedtime'),
                          'record_status' => new external_value(PARAM_TEXT, 'recordstatus'),
                          'questiontype' => new external_value(PARAM_RAW, 'questiontype')
                          )
                    ), 'lesson question detail', VALUE_OPTIONAL
                    ),
            'message' => new external_value(PARAM_RAW, 'quiz id', VALUE_OPTIONAL),
            'start_point' => new external_value(PARAM_INT, 'startpoint', VALUE_OPTIONAL),
            'no_of_record' => new external_value(PARAM_INT, 'no. of record', VALUE_OPTIONAL),
            'total_record' => new external_value(PARAM_INT, 'total record', VALUE_OPTIONAL)
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return get_quizdetails_parameters
     */
    public static function get_quizdetails_parameters() {
        return new external_function_parameters (
            array('username' => new external_value(PARAM_RAW, 'human description of PARAM2'),
                  'password' => new external_value(PARAM_RAW, 'human description of PARAM2'),
                  'quizid' => new external_value(PARAM_INT, 'quiz id'),
                  'startdate' => new external_value(PARAM_RAW, 'start date', VALUE_OPTIONAL),
                  'enddate' => new external_value(PARAM_RAW, 'end date', VALUE_OPTIONAL)
                  )
        );
    }

    /**
     * Get quiz details
     *
     * @param string $username access username
     * @param string $password access password
     * @param int $quizid quizid
     * @param string $startdate startdate (dd-mm-yyyy) optional value
     * @param string $enddate enddate (dd-mm-yyyy) optional value
     * @return array
     */
    public static function get_quizdetails($username, $password, $quizid, $startdate = null, $enddate = null) {
        global $CFG, $DB;
        $params = array('username' => $username,
                        'password' => $password,
                        'quizid' => $quizid,
                        'startdate' => $startdate,
                        'enddate' => $enddate);

        $params = self::validate_parameters(self::get_quizdetails_parameters(), $params);

        $stdate = $params['startdate'];
        $stdate = explode('-', $stdate);
        $stdate = mktime(0, 0, 0, $stdate[1], $stdate[0], $stdate[2]);
        $endate = $params['enddate'];
        $endate = explode('-', $endate);
        $endate = mktime(0, 0, 0, $endate[1], $endate[0], $endate[2]);
        $endate += 86400;

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);
        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            if (!empty($params['startdate']) && !empty($params['startdate'])) {
                $sql = "SELECT COUNT(q.id) AS qcount
                          FROM {question_categories} qc
                          JOIN {context} c ON c.id = qc.contextid
                          JOIN {question} q ON q.category = qc.id
                          JOIN {quiz_slots} qs ON qs.questionid = q.id
                          JOIN {quiz} qz ON qz.id = qs.quizid
                          JOIN {course} cs ON cs.id = qz.course
                          JOIN {course_categories} cc ON cc.id = cs.category
                         WHERE qs.quizid = :quizid
                               AND q.timemodified >= :stdate AND q.timemodified < :endate";

                $qcount = $DB->get_record_sql($sql, array('quizid' => $params['quizid'], 'stdate' => $stdate,
                                                    'endate' => $endate));

                $sql = "SELECT q.id, q.name, q.questiontext, q.qtype, q.timecreated, q.timemodified, qz.id AS quizid,
                               qz.name AS quizname, cs.id AS courseid, cs.fullname AS fullname, cc.id AS categoryid,
                               cc.name AS categoryname, IF (q.timemodified <= q.timecreated, 'New', 'Updated') AS recordstatus
                          FROM {question_categories} qc
                          JOIN {context} c ON c.id = qc.contextid
                          JOIN {question} q ON q.category = qc.id
                          JOIN {quiz_slots} qs ON qs.questionid = q.id
                          JOIN {quiz} qz ON qz.id = qs.quizid
                          JOIN {course} cs ON cs.id = qz.course
                          JOIN {course_categories} cc ON cc.id = cs.category
                         WHERE qs.quizid = :quizid
                               AND q.timemodified >= :stdate AND q.timemodified < :endate";
            } else {
                $sql = "SELECT COUNT(q.id) AS qcount
                          FROM {question_categories} qc
                          JOIN {context} c ON c.id = qc.contextid
                          JOIN {question} q ON q.category = qc.id
                          JOIN {quiz_slots} qs ON qs.questionid = q.id
                          JOIN {quiz} qz ON qz.id = qs.quizid
                          JOIN {course} cs ON cs.id = qz.course
                          JOIN {course_categories} cc ON cc.id = cs.category
                         WHERE qs.quizid = :quizid";

                $qcount = $DB->get_record_sql($sql, array('quizid' => $params['quizid']));

                $sql = "SELECT q.id, q.name, q.questiontext, q.qtype, q.timecreated, q.timemodified, qz.id AS quizid,
                               qz.name AS quizname, cs.id AS courseid, cs.fullname AS fullname, cc.id AS categoryid,
                               cc.name AS categoryname, IF (q.timemodified <= q.timecreated, 'New', 'Updated') AS recordstatus
                          FROM {question_categories} qc
                          JOIN {context} c ON c.id = qc.contextid
                          JOIN {question} q ON q.category = qc.id
                          JOIN {quiz_slots} qs ON qs.questionid = q.id
                          JOIN {quiz} qz ON qz.id = qs.quizid
                          JOIN {course} cs ON cs.id = qz.course
                          JOIN {course_categories} cc ON cc.id = cs.category
                         WHERE qs.quizid = :quizid";
            }

            $questionsvalues = $DB->get_records_sql($sql, array('quizid' => $params['quizid'], 'stdate' => $stdate,
                                                          'endate' => $endate));

            if (!empty($questionsvalues)) {
                $returnvalue = array();
                foreach ($questionsvalues as $value) {
                    if ($value->qtype == 'match') {
                        $sqlab = "SELECT id, questiontext, answertext
                                    FROM {qtype_match_subquestions}
                                   WHERE questionid = :questionid";
                        $answeroptions = $DB->get_records_sql($sqlab, array('questionid' => $value->id));

                        foreach ($answeroptions as $val) {
                            $answers = array();
                            $answers['value'] = $val->questiontext;
                            $answers['score'] = number_format((float)$val->answertext, 2, '.', '');
                            $var1[] = $answers;
                        }
                    } else if ($value->qtype == 'essay') {
                        $answers = array();
                        $answers['value'] = '';
                        $answers['score'] = '0.00';
                        $var1[] = $answers;
                    } else if ($value->qtype == 'ddwtos') {
                        $answers = array();
                        $answers['value'] = '';
                        $answers['score'] = '0.00';
                        $var1[] = $answers;
                    } else if ($value->qtype == 'ddmarker') {
                        $answers = array();
                        $answers['value'] = '';
                        $answers['score'] = '0.00';
                        $var1[] = $answers;
                    } else {
                        $sqlac = "SELECT id, answer, fraction
                                    FROM {question_answers}
                                   WHERE question = :question";
                        $answeroptions = $DB->get_records_sql($sqlac, array('question' => $value->id));

                        foreach ($answeroptions as $val) {
                            $answers = array();
                            $answers['value'] = $val->answer;
                            $answers['score'] = number_format((float)$val->fraction, 2, '.', '');
                            $var1[] = $answers;
                        }
                    }
                    $questionval = array();
                    $questionval['questionid'] = $value->id;
                    $questionval['standard_id'] = $value->categoryid;
                    $questionval['standard_name'] = $value->categoryname;
                    $questionval['subject_id'] = $value->courseid;
                    $questionval['subject_name'] = $value->fullname;
                    $questionval['quizid'] = $value->quizid;
                    $questionval['quizname'] = $value->quizname;
                    $questionval['answer'] = $var1;
                    $questionval['title'] = $value->name;
                    $questionval['questiontext'] = quizimages($value->questiontext, $value->id);
                    $questionval['creation_datetime'] = date('d-m-Y, h:i:s A', $value->timecreated);
                    $questionval['updation_datetime'] = date('d-m-Y, h:i:s A', $value->timemodified);
                    $questionval['record_status'] = $value->recordstatus;

                    if ($value->qtype == 'shortanswer') {
                        $questionval['questiontype'] = 'Short Answer';
                    } else if ($value->qtype == 'truefalse') {
                        $questionval['questiontype'] = 'True/false';
                    } else if ($value->qtype == 'multichoice') {
                        $questionval['questiontype'] = 'Multichoice';
                    } else if ($value->qtype == 'match') {
                        $questionval['questiontype'] = 'Matching';
                    } else if ($value->qtype == 'numerical') {
                        $questionval['questiontype'] = 'Numerical';
                    } else if ($value->qtype == 'essay') {
                        $questionval['questiontype'] = 'Essay';
                    } else if ($value->qtype == 'calculated') {
                        $questionval['questiontype'] = 'Calculated';
                    } else if ($value->qtype == 'calculatedmulti') {
                        $questionval['questiontype'] = 'Calculated MultiChoice';
                    } else if ($value->qtype == 'calculatedsimple') {
                        $questionval['questiontype'] = 'Calculated Simple';
                    } else if ($value->qtype == 'ddwtos') {
                        $questionval['questiontype'] = 'Drop Down Text';
                    } else if ($value->qtype == 'ddmarker') {
                        $questionval['questiontype'] = 'Drop Down Markers';
                    } else {
                        $questionval['questiontype'] = 'Quiz Type question not matched.';
                    }
                    unset($answers);
                    unset($var1);
                    $returnvalue[] = $questionval;
                }
                $returnvalue1['quizdetails'] = $returnvalue;
                $returnvalue1['total_record'] = $qcount->qcount;

                return $returnvalue1;
            } else {
                $a   = array();
                $a[] = array('message' => 'Records not Available or Quiz id does not exists!');
                return $a[0];
            }
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a[0];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_quizdetails_returns() {
        return new external_function_parameters(
            array('quizdetails' => new external_multiple_structure(
                new external_single_structure(
                   array('questionid' => new external_value(PARAM_INT, 'question id', VALUE_OPTIONAL),
                   'standard_id' => new external_value(PARAM_INT, 'category id', VALUE_OPTIONAL),
                   'standard_name' => new external_value(PARAM_RAW, 'category name', VALUE_OPTIONAL),
                   'subject_id' => new external_value(PARAM_INT, 'course id', VALUE_OPTIONAL),
                   'subject_name' => new external_value(PARAM_RAW, 'course name', VALUE_OPTIONAL),
                   'quizid' => new external_value(PARAM_INT, 'quiz id', VALUE_OPTIONAL),
                   'quizname' => new external_value(PARAM_RAW, 'quiz name', VALUE_OPTIONAL),
                   'answer' => new external_multiple_structure(
                       new external_single_structure(
                           array('value' => new external_value(PARAM_RAW, 'option', VALUE_OPTIONAL),
                                 'score' => new external_value(PARAM_RAW, 'score', VALUE_OPTIONAL)
                                 )
                       ), 'answer', VALUE_OPTIONAL
                   ),
                   'title' => new external_value(PARAM_RAW, 'question title', VALUE_OPTIONAL),
                   'questiontext' => new external_value(PARAM_RAW, 'question text', VALUE_OPTIONAL),
                   'creation_datetime' => new external_value(PARAM_RAW, 'createdtime', VALUE_OPTIONAL),
                   'updation_datetime' => new external_value(PARAM_RAW, 'modifiedtime', VALUE_OPTIONAL),
                   'record_status' => new external_value(PARAM_RAW, 'recordstatus', VALUE_OPTIONAL),
                   'questiontype' => new external_value(PARAM_RAW, 'questiontype', VALUE_OPTIONAL),
                   )
                ), 'quiz details', VALUE_OPTIONAL
            ),
            'total_record' => new external_value(PARAM_INT, 'total no. of record', VALUE_OPTIONAL),
            'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL)
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return get_quizdetailswithoutquizid_parameters
     */
    public static function get_quizdetailswithoutquizid_parameters() {
        return new external_function_parameters (
            array('username' => new external_value(PARAM_RAW, 'human description of PARAM2'),
                  'password' => new external_value(PARAM_RAW, 'human description of PARAM2'),
                  'startdate' => new external_value(PARAM_RAW, 'start date', VALUE_OPTIONAL),
                  'enddate' => new external_value(PARAM_RAW, 'end date', VALUE_OPTIONAL)
                  )
        );
    }

    /**
     * Get quiz details Without Quiz id
     *
     * @param string $username access username
     * @param string $password access password
     * @param string $startdate startdate (dd-mm-yyyy) optional value
     * @param string $enddate enddate (dd-mm-yyyy) optional value
     * @return array
     */
    public static function get_quizdetailswithoutquizid($username, $password, $startdate = null, $enddate = null) {
        global $CFG, $DB;
        $params = array('username' => $username,
                        'password' => $password,
                        'startdate' => $startdate,
                        'enddate' => $enddate);

        $params = self::validate_parameters(self::get_quizdetailswithoutquizid_parameters(), $params);

        $stdate = $params['startdate'];
        $stdate = explode('-', $stdate);
        $stdate = mktime(0, 0, 0, $stdate[1], $stdate[0], $stdate[2]);
        $endate = $params['enddate'];
        $endate = explode('-', $endate);
        $endate = mktime(0, 0, 0, $endate[1], $endate[0], $endate[2]);
        $endate += 86400;

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);
        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            if (!empty($params['startdate']) && !empty($params['startdate'])) {
                $sql = "SELECT COUNT(q.id) AS qcount
                          FROM {question_categories} qc
                          JOIN {context} c ON c.id = qc.contextid
                          JOIN {question} q ON q.category = qc.id
                          JOIN {quiz_slots} qs ON qs.questionid = q.id
                          JOIN {quiz} qz ON qz.id = qs.quizid
                          JOIN {course} cs ON cs.id = qz.course
                          JOIN {course_categories} cc ON cc.id = cs.category
                         WHERE q.timemodified >= :stdate AND q.timemodified < :endate";

                $qcount = $DB->get_record_sql($sql, array('stdate' => $stdate, 'endate' => $endate));

                $sql = "SELECT q.id, q.name, q.questiontext, q.qtype, q.timecreated, q.timemodified, qz.id AS quizid,
                               qz.name AS quizname, cs.id AS courseid, cs.fullname AS fullname, cc.id AS categoryid,
                               cc.name AS categoryname, IF (q.timemodified <= q.timecreated, 'New', 'Updated') AS recordstatus
                          FROM {question_categories} qc
                          JOIN {context} c ON c.id = qc.contextid
                          JOIN {question} q ON q.category = qc.id
                          JOIN {quiz_slots} qs ON qs.questionid = q.id
                          JOIN {quiz} qz ON qz.id = qs.quizid
                          JOIN {course} cs ON cs.id = qz.course
                          JOIN {course_categories} cc ON cc.id = cs.category
                         WHERE q.timemodified >= :stdate AND q.timemodified < :endate";
            } else {
                $sql = "SELECT COUNT(q.id) AS qcount
                          FROM {question_categories} qc
                          JOIN {context} c ON c.id = qc.contextid
                          JOIN {question} q ON q.category = qc.id
                          JOIN {quiz_slots} qs ON qs.questionid = q.id
                          JOIN {quiz} qz ON qz.id = qs.quizid
                          JOIN {course} cs ON cs.id = qz.course
                          JOIN {course_categories} cc ON cc.id = cs.category";

                $qcount = $DB->get_record_sql($sql);

                $sql = "SELECT q.id, q.name, q.questiontext, q.qtype, q.timecreated, q.timemodified, qz.id AS quizid,
                               qz.name AS quizname, cs.id AS courseid, cs.fullname AS fullname, cc.id AS categoryid,
                               cc.name AS categoryname, IF (q.timemodified <= q.timecreated, 'New', 'Updated') AS recordstatus
                          FROM {question_categories} qc
                          JOIN {context} c ON c.id = qc.contextid
                          JOIN {question} q ON q.category = qc.id
                          JOIN {quiz_slots} qs ON qs.questionid = q.id
                          JOIN {quiz} qz ON qz.id = qs.quizid
                          JOIN {course} cs ON cs.id = qz.course
                          JOIN {course_categories} cc ON cc.id = cs.category";
            }

            $questionsvalues = $DB->get_records_sql($sql, array('stdate' => $stdate, 'endate' => $endate));

            if (!empty($questionsvalues)) {
                $returnvalue = array();
                foreach ($questionsvalues as $value) {
                    if ($value->qtype == 'match') {
                        $sqlab = "SELECT id, questiontext, answertext
                                    FROM {qtype_match_subquestions}
                                   WHERE questionid = :questionid";
                        $answeroptions = $DB->get_records_sql($sqlab, array('questionid' => $value->id));

                        foreach ($answeroptions as $val) {
                            $answers = array();
                            $answers['value'] = $val->questiontext;
                            $answers['score'] = number_format((float)$val->answertext, 2, '.', '');
                            $var1[] = $answers;
                        }
                    } else if ($value->qtype == 'essay') {
                        $answers = array();
                        $answers['value'] = '';
                        $answers['score'] = '0.00';
                        $var1[] = $answers;
                    } else if ($value->qtype == 'ddwtos') {
                        $answers = array();
                        $answers['value'] = '';
                        $answers['score'] = '0.00';
                        $var1[] = $answers;
                    } else if ($value->qtype == 'ddmarker') {
                        $answers = array();
                        $answers['value'] = '';
                        $answers['score'] = '0.00';
                        $var1[] = $answers;
                    } else {
                        $sqlac = "SELECT id, answer, fraction
                                    FROM {question_answers}
                                   WHERE question = :question";
                        $answeroptions = $DB->get_records_sql($sqlac, array('question' => $value->id));

                        foreach ($answeroptions as $val) {
                            $answers = array();
                            $answers['value'] = $val->answer;
                            $answers['score'] = number_format((float)$val->fraction, 2, '.', '');
                            $var1[] = $answers;
                        }
                    }
                    $questionval = array();
                    $questionval['questionid'] = $value->id;
                    $questionval['standard_id'] = $value->categoryid;
                    $questionval['standard_name'] = $value->categoryname;
                    $questionval['subject_id'] = $value->courseid;
                    $questionval['subject_name'] = $value->fullname;
                    $questionval['quizid'] = $value->quizid;
                    $questionval['quizname'] = $value->quizname;
                    $questionval['answer'] = $var1;
                    $questionval['title'] = $value->name;
                    $questionval['questiontext'] = quizimages($value->questiontext, $value->id);
                    $questionval['creation_datetime'] = date('d-m-Y, h:i:s A', $value->timecreated);
                    $questionval['updation_datetime'] = date('d-m-Y, h:i:s A', $value->timemodified);
                    $questionval['record_status'] = $value->recordstatus;

                    if ($value->qtype == 'shortanswer') {
                        $questionval['questiontype'] = 'Short Answer';
                    } else if ($value->qtype == 'truefalse') {
                        $questionval['questiontype'] = 'True/false';
                    } else if ($value->qtype == 'multichoice') {
                        $questionval['questiontype'] = 'Multichoice';
                    } else if ($value->qtype == 'match') {
                        $questionval['questiontype'] = 'Matching';
                    } else if ($value->qtype == 'numerical') {
                        $questionval['questiontype'] = 'Numerical';
                    } else if ($value->qtype == 'essay') {
                        $questionval['questiontype'] = 'Essay';
                    } else if ($value->qtype == 'calculated') {
                        $questionval['questiontype'] = 'Calculated';
                    } else if ($value->qtype == 'calculatedmulti') {
                        $questionval['questiontype'] = 'Calculated MultiChoice';
                    } else if ($value->qtype == 'calculatedsimple') {
                        $questionval['questiontype'] = 'Calculated Simple';
                    } else if ($value->qtype == 'ddwtos') {
                        $questionval['questiontype'] = 'Drop Down Text';
                    } else if ($value->qtype == 'ddmarker') {
                        $questionval['questiontype'] = 'Drop Down Markers';
                    } else {
                        $questionval['questiontype'] = 'Quiz Type question not matched.';
                    }
                    unset($answers);
                    unset($var1);
                    $returnvalue[] = $questionval;
                }
                $returnvalue1['quizdetails'] = $returnvalue;
                $returnvalue1['total_record'] = $qcount->qcount;

                return $returnvalue1;
            } else {
                $a   = array();
                $a[] = array('message' => 'Records not Available or Quiz id does not exists!');
                return $a[0];
            }
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a[0];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_quizdetailswithoutquizid_returns() {
        return new external_function_parameters(
            array('quizdetails' => new external_multiple_structure(
                new external_single_structure(
                   array('questionid' => new external_value(PARAM_INT, 'question id', VALUE_OPTIONAL),
                   'standard_id' => new external_value(PARAM_INT, 'category id', VALUE_OPTIONAL),
                   'standard_name' => new external_value(PARAM_RAW, 'category name', VALUE_OPTIONAL),
                   'subject_id' => new external_value(PARAM_INT, 'course id', VALUE_OPTIONAL),
                   'subject_name' => new external_value(PARAM_RAW, 'course name', VALUE_OPTIONAL),
                   'quizid' => new external_value(PARAM_INT, 'quiz id', VALUE_OPTIONAL),
                   'quizname' => new external_value(PARAM_RAW, 'quiz name', VALUE_OPTIONAL),
                   'answer' => new external_multiple_structure(
                       new external_single_structure(
                           array('value' => new external_value(PARAM_RAW, 'option', VALUE_OPTIONAL),
                                 'score' => new external_value(PARAM_RAW, 'score', VALUE_OPTIONAL)
                                 )
                       ), 'answer', VALUE_OPTIONAL
                   ),
                   'title' => new external_value(PARAM_RAW, 'question title', VALUE_OPTIONAL),
                   'questiontext' => new external_value(PARAM_RAW, 'question text', VALUE_OPTIONAL),
                   'creation_datetime' => new external_value(PARAM_RAW, 'createdtime', VALUE_OPTIONAL),
                   'updation_datetime' => new external_value(PARAM_RAW, 'modifiedtime', VALUE_OPTIONAL),
                   'record_status' => new external_value(PARAM_RAW, 'recordstatus', VALUE_OPTIONAL),
                   'questiontype' => new external_value(PARAM_RAW, 'questiontype', VALUE_OPTIONAL),
                   )
                ), 'quiz details', VALUE_OPTIONAL
            ),
            'total_record' => new external_value(PARAM_INT, 'total no. of record', VALUE_OPTIONAL),
            'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL)
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return get_quizquestiondetails_parameters
     */
    public static function get_quizquestiondetails_parameters() {
        return new external_function_parameters (
            array('username' => new external_value(PARAM_RAW, 'username'),
                  'password' => new external_value(PARAM_RAW, 'password'),
                  'questionid' => new external_value(PARAM_RAW, 'question id'),
                 )
        );
    }

    /**
     * Get quiz details by question id
     *
     * @param string $username access username
     * @param string $password access password
     * @param int $questionid question id
     * @return array
     */
    public static function get_quizquestiondetails($username, $password, $questionid) {
        global $CFG, $DB;
        $params = self::validate_parameters(self::get_quizquestiondetails_parameters(),
            array('username' => $username,
                  'password' => $password,
                  'questionid' => $questionid
                 ));

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);
        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            $returnvalue = array();
            $var1 = array();

            $sql = "SELECT q.id, q.name, q.questiontext, q.qtype, q.timecreated, q.timemodified, qz.id AS quizid,
                           qz.name AS quizname, cs.id AS courseid, cs.fullname AS fullname, cc.id AS categoryid,
                           cc.name AS categoryname, IF (q.timemodified <= q.timecreated, 'New', 'Updated') AS recordstatus
                      FROM {question_categories} qc
                      JOIN {context} c ON c.id = qc.contextid
                      JOIN {question} q ON q.category = qc.id
                      JOIN {quiz_slots} qs ON qs.questionid = q.id
                      JOIN {quiz} qz ON qz.id = qs.quizid
                      JOIN {course} cs ON cs.id = qz.course
                      JOIN {course_categories} cc ON cc.id = cs.category
                     WHERE q.id = :questionid";

            $qdetail = $DB->get_record_sql($sql, array('questionid' => $params['questionid']));

            if (!empty($qdetail)) {
                if ($qdetail->qtype == 'match') {
                    $sqlab = "SELECT id, questiontext, answertext
                                FROM {qtype_match_subquestions}
                               WHERE questionid = :questionid";
                    $answeroptions = $DB->get_records_sql($sqlab, array('questionid' => $qdetail->id));

                    foreach ($answeroptions as $val) {
                        $answers = array();
                        $answers['value'] = $val->questiontext;
                        $answers['score'] = number_format((float)$val->answertext, 2, '.', '');
                        $var1[] = $answers;
                    }
                } else if ($qdetail->qtype == 'essay') {
                    $answers = array();
                    $answers['value'] = '';
                    $answers['score'] = '0.00';
                    $var1[] = $answers;
                } else {
                    $answeroptions = $DB->get_records_sql("SELECT id, answer, fraction FROM {question_answers}
                    WHERE question = '".$qdetail->id."'");

                    foreach ($answeroptions as $val) {
                        $answers = array();
                        $answers['value'] = $val->answer;
                        $answers['score'] = number_format((float)$val->fraction, 2, '.', '');
                        $var1[] = $answers;
                    }
                }

                $questionval = array();
                $questionval['questionid'] = $qdetail->id;
                $questionval['standard_id'] = $qdetail->categoryid;
                $questionval['standard_name'] = $qdetail->categoryname;
                $questionval['subject_id'] = $qdetail->courseid;
                $questionval['subject_name'] = $qdetail->fullname;
                $questionval['quizid'] = $qdetail->quizid;
                $questionval['quizname'] = $qdetail->quizname;
                $questionval['answer'] = $var1;
                $questionval['title'] = $qdetail->name;
                $questionval['questiontext'] = quizimages($qdetail->questiontext, $qdetail->id);
                $questionval['creation_datetime'] = date('d-m-Y, h:i:s A', $qdetail->timecreated);
                $questionval['updation_datetime'] = date('d-m-Y, h:i:s A', $qdetail->timemodified);
                $questionval['record_status'] = $qdetail->recordstatus;

                if ($qdetail->qtype == 'shortanswer') {
                    $questionval['questiontype'] = 'Short Answer';
                } else if ($qdetail->qtype == 'truefalse') {
                    $questionval['questiontype'] = 'True/false';
                } else if ($qdetail->qtype == 'multichoice') {
                    $questionval['questiontype'] = 'Multichoice';
                } else if ($qdetail->qtype == 'match') {
                    $questionval['questiontype'] = 'Matching';
                } else if ($qdetail->qtype == 'numerical') {
                    $questionval['questiontype'] = 'Numerical';
                } else if ($qdetail->qtype == 'essay') {
                    $questionval['questiontype'] = 'Essay';
                } else if ($qdetail->qtype == 'calculated') {
                    $questionval['questiontype'] = 'Calculated';
                } else if ($qdetail->qtype == 'calculatedmulti') {
                    $questionval['questiontype'] = 'Calculated MultiChoice';
                } else if ($qdetail->qtype == 'calculatedsimple') {
                    $questionval['questiontype'] = 'Calculated Simple';
                } else if ($qdetail->qtype == 'ddwtos') {
                    $questionval['questiontype'] = 'Drop Down Text';
                } else if ($qdetail->qtype == 'ddmarker') {
                    $questionval['questiontype'] = 'Drop Down Markers';
                } else {
                    $questionval['questiontype'] = 'Quiz Type question not matched.';
                }
                unset($answers);
                unset($var1);
                $returnvalue[] = $questionval;

                return $returnvalue;
            } else {
                $a   = array();
                $a[] = array('message' => 'Records not Available or Question id does not exists!');
                return $a;
            }
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a;
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_quizquestiondetails_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array('questionid' => new external_value(PARAM_INT, 'question id', VALUE_OPTIONAL),
                      'standard_id' => new external_value(PARAM_INT, 'category id', VALUE_OPTIONAL),
                      'standard_name' => new external_value(PARAM_RAW, 'category name', VALUE_OPTIONAL),
                      'subject_id' => new external_value(PARAM_INT, 'course id', VALUE_OPTIONAL),
                      'subject_name' => new external_value(PARAM_RAW, 'course name', VALUE_OPTIONAL),
                      'quizid' => new external_value(PARAM_INT, 'quiz id', VALUE_OPTIONAL),
                      'quizname' => new external_value(PARAM_RAW, 'quiz name', VALUE_OPTIONAL),
                      'answer' => new external_multiple_structure(
                          new external_single_structure(
                              array('value' => new external_value(PARAM_RAW, 'option', VALUE_OPTIONAL),
                                    'score' => new external_value(PARAM_RAW, 'score', VALUE_OPTIONAL)
                                   )
                          ), 'answer', VALUE_OPTIONAL
                          ),
                      'title' => new external_value(PARAM_RAW, 'question title', VALUE_OPTIONAL),
                      'questiontext' => new external_value(PARAM_RAW, 'question', VALUE_OPTIONAL),
                      'creation_datetime' => new external_value(PARAM_RAW, 'createdtime', VALUE_OPTIONAL),
                      'updation_datetime' => new external_value(PARAM_RAW, 'modifiedtime', VALUE_OPTIONAL),
                      'record_status' => new external_value(PARAM_RAW, 'recordstatus', VALUE_OPTIONAL),
                      'questiontype' => new external_value(PARAM_RAW, 'questiontype', VALUE_OPTIONAL),
                      'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL)
                      ), 'question detail'
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return get_quizquestions_parameters
     */
    public static function get_quizquestions_parameters() {
        return new external_function_parameters (
            array('username' => new external_value(PARAM_RAW, 'username'),
                  'password' => new external_value(PARAM_RAW, 'password'),
                  'no_of_record' => new external_value(PARAM_INT, 'no. of record'),
                  'start_point' => new external_value(PARAM_INT, 'start point default 0', VALUE_OPTIONAL, 0)
                  )
        );
    }

    /**
     * Get quiz details in batch
     *
     * @param string $username access username
     * @param string $password access password
     * @param int $noofquestions show no. of records
     * @param int $startposition access starting position
     * @return array
     */
    public static function get_quizquestions($username, $password, $noofquestions, $startposition) {
        global $CFG, $DB, $USER;
        $params = array('username' => $username,
                        'password' => $password,
                        'no_of_record' => $noofquestions,
                        'start_point' => $startposition
                        );

        $params = self::validate_parameters(self::get_quizquestions_parameters(), $params);
        $noofquestions = $params['no_of_record'];
        $startposition = $params['start_point'];

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);
        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            $returnvalue = array();
            $returnvalue1 = array();

            $sql = "SELECT COUNT(q.id) AS qcount
                      FROM {question_categories} qc
                      JOIN {context} c ON c.id = qc.contextid
                      JOIN {question} q ON q.category = qc.id
                      JOIN {quiz_slots} qs ON qs.questionid = q.id
                      JOIN {quiz} qz ON qz.id = qs.quizid
                      JOIN {course} cs ON cs.id = qz.course
                      JOIN {course_categories} cc ON cc.id = cs.category";
            $qcount = $DB->get_record_sql($sql);
            $sqlab = "SELECT id, batch, noofquestion
                        FROM {lessonquizapi_batchdetails}
                       WHERE functionname = :gquizquestion ORDER BY id DESC LIMIT 0,1";

            $batch = $DB->get_record_sql($sqlab, array('gquizquestion' => 'get_quizquestions'));
            $record = new stdClass();
            if (isset($startposition)) {
                $limit = " LIMIT ".$startposition.",".$noofquestions;
            } else {
                if (empty($batch)) {
                    $limit = "";
                    $limit = " LIMIT 0,".$noofquestions;
                    $record->batch = 0;
                    $startpoint = 1;
                    $endpoint = $noofquestions;
                } else {
                    if ($batch->batch == 0) {
                        $batchno = $batch->noofquestion;
                        $startpoint = $batch->noofquestion + 1;
                        $endpoint = $batch->noofquestion + $noofquestions;
                    } else {
                        $batchno = ($batch->batch + $batch->noofquestion);
                        $startpoint = $batchno + 1;
                        $endpoint = $batchno + $noofquestions;
                    }
                    $limit = " LIMIT ".$batchno.",".$noofquestions;
                    $record->batch = $batchno;
                }
            }

            $sql = "SELECT q.id, q.name, q.questiontext, q.qtype, q.timecreated, q.timemodified, qz.id AS quizid,
                           qz.name AS quizname, cs.id AS courseid, cs.fullname AS fullname, cc.id AS categoryid,
                           cc.name AS categoryname, IF (q.timemodified <= q.timecreated, 'New', 'Updated') AS recordstatus
                      FROM {question_categories} qc
                      JOIN {context} c ON c.id = qc.contextid
                      JOIN {question} q ON q.category = qc.id
                      JOIN {quiz_slots} qs ON qs.questionid = q.id
                      JOIN {quiz} qz ON qz.id = qs.quizid
                      JOIN {course} cs ON cs.id = qz.course
                      JOIN {course_categories} cc ON cc.id = cs.category".$limit;

            $questionsvalues = $DB->get_records_sql($sql);
            if (empty($questionsvalues)) {
                $message = 'Records Not Available!';
            } else if (isset($startposition)) {
                $message = 'Records Available!';
            } else {
                if ($batchno < $qcount->qcount) {
                    if ($batchno + $noofquestions < $qcount->qcount) {
                        $record->userid = $USER->id;
                        $record->noofquestion = $noofquestions;
                        $record->functionname = 'get_quizquestions';
                        $record->timecreated = time();
                        $record->timemodified = time();
                        $DB->insert_record('lessonquizapi_batchdetails', $record);
                        $message = 'Records Available!';
                    } else {
                        $record->userid = $USER->id;
                        $record->noofquestion = count($questionsvalues);
                        $record->functionname = 'get_quizquestions';
                        $record->timecreated = time();
                        $record->timemodified = time();
                        $DB->insert_record('lessonquizapi_batchdetails', $record);
                        $message = 'More Records Not Available!';
                        $endpoint = ($startpoint + count($questionsvalues) - 1);
                    }
                }
            }

            $returnvalue = array();
            foreach ($questionsvalues as $value) {
                $questionval = array();
                $questionval['questionid'] = $value->id;
                $questionval['standard_id'] = $value->categoryid;
                $questionval['standard_name'] = $value->categoryname;
                $questionval['subject_id'] = $value->courseid;
                $questionval['subject_name'] = $value->fullname;
                $questionval['quizid'] = $value->quizid;
                $questionval['quizname'] = $value->quizname;
                $questionval['title'] = $value->name;
                $questionval['questiontext'] = quizimages($value->questiontext, $value->id);
                $questionval['creation_datetime'] = date('d-m-Y, h:i:s A', $value->timecreated);
                $questionval['updation_datetime'] = date('d-m-Y, h:i:s A', $value->timemodified);
                $questionval['record_status'] = $value->recordstatus;

                if ($value->qtype == 'shortanswer') {
                    $questionval['questiontype'] = 'Short Answer';
                } else if ($value->qtype == 'truefalse') {
                    $questionval['questiontype'] = 'True/false';
                } else if ($value->qtype == 'multichoice') {
                    $questionval['questiontype'] = 'Multichoice';
                } else if ($value->qtype == 'match') {
                    $questionval['questiontype'] = 'Matching';
                } else if ($value->qtype == 'numerical') {
                    $questionval['questiontype'] = 'Numerical';
                } else if ($value->qtype == 'essay') {
                    $questionval['questiontype'] = 'Essay';
                } else if ($value->qtype == 'calculated') {
                    $questionval['questiontype'] = 'Calculated';
                } else if ($value->qtype == 'calculatedmulti') {
                    $questionval['questiontype'] = 'Calculated MultiChoice';
                } else if ($value->qtype == 'calculatedsimple') {
                    $questionval['questiontype'] = 'Calculated Simple';
                } else if ($value->qtype == 'ddwtos') {
                    $questionval['questiontype'] = 'Drop Down Text';
                } else if ($value->qtype == 'ddmarker') {
                    $questionval['questiontype'] = 'Drop Down Markers';
                } else {
                    $questionval['questiontype'] = 'Quiz Type question not matched.';
                }
                unset($answers);
                unset($var1);
                $returnvalue[] = $questionval;
            }
            $returnvalue1['quizdetails'] = $returnvalue;
            $returnvalue1['message'] = $message;
            if (isset($startposition)) {
                $returnvalue1['start_point'] = $startposition;
            } else {
                $returnvalue1['start_point'] = $startpoint;
            }
            $returnvalue1['no_of_record'] = $noofquestions;
            $returnvalue1['total_record'] = $qcount->qcount;

            return $returnvalue1;
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a[0];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_quizquestions_returns() {
        return new external_function_parameters(
            array('quizdetails' => new external_multiple_structure(
                new external_single_structure(
                    array('questionid' => new external_value(PARAM_INT, 'question id'),
                          'standard_id' => new external_value(PARAM_INT, 'category id'),
                          'standard_name' => new external_value(PARAM_RAW, 'category name'),
                          'subject_id' => new external_value(PARAM_INT, 'course id'),
                          'subject_name' => new external_value(PARAM_RAW, 'course name'),
                          'quizid' => new external_value(PARAM_INT, 'quiz id'),
                          'quizname' => new external_value(PARAM_RAW, 'quiz name'),
                          'title' => new external_value(PARAM_RAW, 'quiz question title'),
                          'questiontext' => new external_value(PARAM_RAW, 'question text'),
                          'creation_datetime' => new external_value(PARAM_RAW, 'createdtime'),
                          'updation_datetime' => new external_value(PARAM_RAW, 'modifiedtime'),
                          'record_status' => new external_value(PARAM_RAW, 'recordstatus'),
                          'questiontype' => new external_value(PARAM_RAW, 'questiontype')
                         )
                ), 'quiz detail', VALUE_OPTIONAL
                ),
            'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
            'start_point' => new external_value(PARAM_INT, 'startpoint', VALUE_OPTIONAL),
            'no_of_record' => new external_value(PARAM_INT, 'total no. of record', VALUE_OPTIONAL),
            'total_record' => new external_value(PARAM_INT, 'endpoint', VALUE_OPTIONAL),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return get_totallessonquestion_parameters
     */
    public static function get_totallessonquestion_parameters() {
        return new external_function_parameters(
            array('username' => new external_value(PARAM_RAW, 'username'),
                  'password' => new external_value(PARAM_RAW, 'password'),
                  'lessonid' => new external_value(PARAM_INT, 'lesson id'),
                  )
        );
    }

    /**
     * Get total no. of lesson questions
     *
     * @param string $username access username
     * @param string $password access password
     * @param int $lessonid lesson id
     * @return array
     */
    public static function get_totallessonquestion($username, $password, $lessonid) {
        global $CFG, $DB;
        $params = array('username' => $username,
                        'password' => $password,
                        'lessonid' => $lessonid,
                        );

        $params = self::validate_parameters(self::get_totallessonquestion_parameters(), $params);
        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);
        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            $sql = "SELECT COUNT(lp.id) AS qcount
                      FROM {lesson_pages} lp
                      JOIN {lesson} l ON l.id = lp.lessonid
                      JOIN {course} c ON c.id = l.course
                      JOIN {course_categories} cc ON cc.id = c.category
                     WHERE lp.lessonid = '".$params['lessonid']."' AND lp.qtype IN (1,2,3,5,8,10)";
            $qcount = $DB->get_record_sql($sql);
            if ($qcount->qcount > 0) {
                $returnvalue1['total_lesson_question'] = $qcount->qcount;
                return $returnvalue1;
            } else {
                $returnvalue1['message'] = 'Record not Available or Lesson id does not exists!';
                return $returnvalue1;
            }
        } else {
            $returnvalue1['message'] = $ary['false'];
            return $returnvalue1;
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_totallessonquestion_returns() {
        return new external_function_parameters(
            array('total_lesson_question' => new external_value(PARAM_INT, 'total lesson question', VALUE_OPTIONAL),
                  'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
                 )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return get_totalquizquestion_parameters
     */
    public static function get_totalquizquestion_parameters() {
        return new external_function_parameters(
            array('username' => new external_value(PARAM_RAW, 'username'),
                  'password' => new external_value(PARAM_RAW, 'password'),
                  'quizid' => new external_value(PARAM_INT, 'quiz id'),
                 )
        );
    }

    /**
     * Get total no. of quiz questions
     *
     * @param string $username access username
     * @param string $password access password
     * @param int $quizid quiz id
     * @return array
     */
    public static function get_totalquizquestion($username, $password, $quizid) {
        global $CFG, $DB;
        $params = array('username' => $username,
                        'password' => $password,
                        'quizid' => $quizid,
                       );

        $params = self::validate_parameters(self::get_totalquizquestion_parameters(), $params);

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);
        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            $sql = "SELECT COUNT(q.id) AS qcount
                      FROM {question_categories} qc
                      JOIN {context} c ON c.id = qc.contextid
                      JOIN {question} q ON q.category = qc.id
                      JOIN {quiz_slots} qs ON qs.questionid = q.id
                      JOIN {quiz} qz ON qz.id = qs.quizid
                      JOIN {course} cs ON cs.id = qz.course
                      JOIN {course_categories} cc ON cc.id = cs.category
                     WHERE qs.quizid = :quizid";
            $qcount = $DB->get_record_sql($sql, array('quizid' => $params['quizid']));
            if ($qcount->qcount > 0) {
                $returnvalue1['total_quiz_question'] = $qcount->qcount;
                return $returnvalue1;
            } else {
                $returnvalue1['message'] = 'Record not Available or Quiz id does not exists!';
                return $returnvalue1;
            }
        } else {
            $returnvalue1['message'] = $ary['false'];
            return $returnvalue1;
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_totalquizquestion_returns() {
        return new external_function_parameters(
            array('total_quiz_question' => new external_value(PARAM_INT, 'total quiz question', VALUE_OPTIONAL),
                  'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
                 )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return get_lessquestionwithoutlessonid_parameters
     */
    public static function get_lessquestionwithoutlessonid_parameters() {
        return new external_function_parameters(
            array('username' => new external_value(PARAM_RAW, 'username'),
                  'password' => new external_value(PARAM_RAW, 'password'),
                  'startdate' => new external_value(PARAM_RAW, 'start date', VALUE_OPTIONAL),
                  'enddate' => new external_value(PARAM_RAW, 'end date', VALUE_OPTIONAL)
                  )
        );
    }

    /**
     * Get lesson question, answers
     *
     * @param string $username access username
     * @param string $password access password
     * @param string $startdate startdate (dd-mm-yyyy) optional value
     * @param string $enddate enddate (dd-mm-yyyy) optional value
     * @return array
     */
    public static function get_lessquestionwithoutlessonid($username, $password, $startdate, $enddate) {
        global $CFG, $DB;
        $params = array('username' => $username,
                        'password' => $password,
                        'startdate' => $startdate,
                        'enddate' => $enddate);

        $params = self::validate_parameters(self::get_lessquestionwithoutlessonid_parameters(), $params);
        $stdate = $params['startdate'];
        $edate  = $params['enddate'];
        $stdate = explode('-', $stdate);
        $stdate = mktime(0, 0, 0, $stdate[1], $stdate[0], $stdate[2]);
        $edate  = explode('-', $edate);
        $edate  = mktime(0, 0, 0, $edate[1], $edate[0], $edate[2]);
        $edate  += 86400;

        require_once($CFG->dirroot.'/local/lessonquizapi/locallib.php');
        $authdata = lessonquizapi_wsauthenticate($params['username'], $params['password']);
        $ary = array();
        foreach ($authdata as $val) {
            $ary['true'] = $val[message2];
            $ary['false'] = $val[message1];
        }
        if ($ary['true'] == 1) {
            if (!empty($params['startdate']) && !empty($params['enddate'])) {
                $sql = "SELECT COUNT(lp.id) AS qcount
                          FROM {lesson_pages} lp
                          JOIN {lesson} l ON l.id = lp.lessonid
                          JOIN {course} c ON c.id = l.course
                          JOIN {course_categories} cc ON cc.id = c.category
                         WHERE lp.qtype IN (1,2,3,5,8,10)
                               AND IF (lp.timemodified = 0, lp.timecreated, lp.timemodified) >= :stdate
                               AND IF(lp.timemodified = 0, lp.timecreated, lp.timemodified) < :edate";

                $qcount = $DB->get_record_sql($sql, array('stdate' => $stdate, 'edate' => $edate));

                $sql = "SELECT lp.id, lp.title, lp.contents, lp.qtype, lp.timecreated, lp.timemodified, c.fullname,
                               c.id AS courseid, l.id AS lessonid, cc.id AS categoryid, cc.name AS categoryname,
                               l.name AS lessonname, IF (lp.timemodified = 0, 'New', 'Updated') AS recordstatus
                          FROM {lesson_pages} lp
                          JOIN {lesson} l ON l.id = lp.lessonid
                          JOIN {course} c ON c.id = l.course
                          JOIN {course_categories} cc ON cc.id = c.category
                         WHERE lp.qtype IN (1,2,3,5,8,10)
                               AND IF (lp.timemodified = 0, lp.timecreated, lp.timemodified) >= :stdate
                               AND IF(lp.timemodified = 0, lp.timecreated, lp.timemodified) < :edate";
            } else {
                $sql = "SELECT COUNT(lp.id) AS qcount
                          FROM {lesson_pages} lp
                          JOIN {lesson} l ON l.id = lp.lessonid
                          JOIN {course} c ON c.id = l.course
                          JOIN {course_categories} cc ON cc.id = c.category
                         WHERE lp.qtype IN (1,2,3,5,8,10)";

                $qcount = $DB->get_record_sql($sql);

                $sql = "SELECT lp.id, lp.title, lp.contents, lp.qtype, lp.timecreated, lp.timemodified, c.fullname,
                               c.id AS courseid, l.id AS lessonid, cc.id AS categoryid, cc.name AS categoryname,
                               l.name AS lessonname, IF (lp.timemodified = 0, 'New', 'Updated') AS recordstatus
                          FROM {lesson_pages} lp
                          JOIN {lesson} l ON l.id = lp.lessonid
                          JOIN {course} c ON c.id = l.course
                          JOIN {course_categories} cc ON cc.id = c.category
                         WHERE lp.qtype IN (1,2,3,5,8,10)";
            }

            $questionsvalues = $DB->get_records_sql($sql, array('stdate' => $stdate, 'edate' => $edate));

            $returnvalue = array();
            if (!empty($questionsvalues)) {
                foreach ($questionsvalues as $value) {
                    $sqlab = "SELECT id, answer, score
                                FROM {lesson_answers}
                               WHERE pageid = :pageid";
                    $answeroptions = $DB->get_records_sql($sqlab, array('pageid' => $value->id));

                    foreach ($answeroptions as $val) {
                        $answers           = array();
                        $answers['value1'] = $val->answer;
                        $answers['score']  = number_format((float)$val->score, 2, '.', '');
                        $var1[]            = $answers;
                    }

                    $questionval                      = array();
                    $questionval['lpid']              = $value->id;
                    $questionval['standard_id']       = $value->categoryid;
                    $questionval['standard_name']     = $value->categoryname;
                    $questionval['subject_id']        = $value->courseid;
                    $questionval['subject_name']      = $value->fullname;
                    $questionval['lesson_id']         = $value->lessonid;
                    $questionval['lesson_name']       = $value->lessonname;
                    $questionval['answer']            = $var1;
                    $questionval['title']             = $value->title;
                    $questionval['creation_datetime'] = date('d-m-Y, h:i:s A', $value->timecreated);
                    $questionval['updation_datetime'] = ($value->timemodified == 0) ? 0 : date('d-m-Y, h:i:s A',
                                                         $value->timemodified);
                    $questionval['record_status']     = $value->recordstatus;
                    $questionval['questiontext']          = lessonimages($value->contents);

                    if ($value->qtype == 1) {
                        $questionval['questiontype'] = 'Short Answer';
                    } else if ($value->qtype == 2) {
                        $questionval['questiontype'] = 'True/false';
                    } else if ($value->qtype == 3) {
                        $questionval['questiontype'] = 'Multichoice';
                    } else if ($value->qtype == 5) {
                        $questionval['questiontype'] = 'Matching';
                    } else if ($value->qtype == 8) {
                        $questionval['questiontype'] = 'Numerical';
                    } else if ($value->qtype == 10) {
                        $questionval['questiontype'] = 'Essay';
                    } else if ($value->qtype == 20) {
                        $questionval['questiontype'] = 'Page Contents';
                    } else {
                        $questionval['questiontype'] = 'Lesson Type question not matched.';
                    }
                    unset($answers);
                    unset($var1);
                    $returnvalue[] = $questionval;
                }
                $returnvalue1['lquestiondetails'] = $returnvalue;
                $returnvalue1['total_record'] = $qcount->qcount;

                return $returnvalue1;
            } else {
                $a   = array();
                $a[] = array('message' => 'Records not Available or Lesson id is not exists!');
                return $a[0];
            }
        } else {
            $a   = array();
            $a[] = array('message' => $ary['false']);
            return $a[0];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_lessquestionwithoutlessonid_returns() {
        return new external_function_parameters(
            array('lquestiondetails' => new external_multiple_structure(
                new external_single_structure(
                    array('lpid' => new external_value(PARAM_INT, 'lesson page id'),
                          'standard_id' => new external_value(PARAM_INT, 'category id'),
                          'standard_name' => new external_value(PARAM_RAW, 'category name'),
                          'subject_id' => new external_value(PARAM_INT, 'course id'),
                          'subject_name' => new external_value(PARAM_RAW, 'course name'),
                          'lesson_id' => new external_value(PARAM_INT, 'lesson id'),
                          'lesson_name' => new external_value(PARAM_RAW, 'lesson name'),
                          'answer' => new external_multiple_structure(
                              new external_single_structure(
                                  array('value1' => new external_value(PARAM_RAW, 'option'),
                                        'score' => new external_value(PARAM_RAW, 'answer'),
                                       )
                              ), 'answer', VALUE_OPTIONAL
                          ),
                          'title' => new external_value(PARAM_TEXT, 'lesson page title'),
                          'questiontext' => new external_value(PARAM_RAW, 'content'),
                          'creation_datetime' => new external_value(PARAM_RAW, 'createdtime'),
                          'updation_datetime' => new external_value(PARAM_RAW, 'modifiedtime'),
                          'record_status' => new external_value(PARAM_TEXT, 'recordstatus'),
                          'questiontype' => new external_value(PARAM_RAW, 'questiontype')
                          )
                ), 'lesson question details', VALUE_OPTIONAL
                ),
            'total_record' => new external_value(PARAM_INT, 'total no. of record', VALUE_OPTIONAL),
            'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
            )
        );
    }
}