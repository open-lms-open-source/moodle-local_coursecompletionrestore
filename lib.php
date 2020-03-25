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
 * @package     local_coursecompletionrestore
 * @copyright   2020 Lupiya Mujala <lupiya@ecreators.com.au>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die;

function get_course_list(){
    global $DB;

    $courses = array();
    $course_list = $DB->get_records('course');

    foreach ($course_list as $course){
        //Check if the course has completions to backup.
        $completions = $DB->get_records('course_completions', array('course' => $course->id));
        if($completions) {
            $courses[$course->id] = $course->fullname;
        }
    }
    return $courses;
}

function get_course_completion_backups(){
    global $DB, $CFG;

    $backups = $DB->get_records('local_ccr_snapshots');

    $final_backups = array();

    foreach ($backups as $backup){

        $lastusestring = "";
        $firstusestring = "";

        $datecreated = new DateTime();
        $datecreated->setTimestamp($backup->timecreated);
        $datecreated->setTimeZone(new DateTimeZone($CFG->timezone));
        $datecreatedstring = $datecreated->format('d/m/Y H:i:s');
        $course = $DB->get_record('course', array('id' => $backup->course));
        if(!empty($backup->recentrestoredate)) {
            $lastuse = new DateTime();
            $lastuse->setTimestamp($backup->recentrestoredate);
            $lastuse->setTimeZone(new DateTimeZone($CFG->timezone));
            $lastusestring = $lastuse->format('d/m/Y H:i:s');
        }
        if(!empty($backup->firstrestoredate)) {
            $firstuse = new DateTime();
            $firstuse->setTimestamp($backup->firstrestoredate);
            $firstuse->setTimeZone(new DateTimeZone($CFG->timezone));
            $firstusestring = $firstuse->format('d/m/Y H:i:s');
        }
        $completions = $DB->get_records('local_ccr_crs_completions', array('snapshotid' => $backup->id));
        $final_backups[] = array('id' => $backup->id, 'courseid' => $backup->course, 'course' => $course->fullname, 'notes' => $backup->notes,
                                'uses' => $backup->uses, 'datecreated' => $datecreatedstring, 'lastuse' => $lastusestring, 'firstuse' => $firstusestring, 'completions' => count($completions));
    }

    return $final_backups;
}

function get_snaphot_details($id){
    global $DB;

    $snapshot =  $DB->get_record_sql("SELECT *, DATE_FORMAT(FROM_UNIXTIME(timecreated),'%d-%m-%Y') as timecreated FROM {local_ccr_snapshots} WHERE id =? ", array($id));
    $course = $DB->get_record('course', array('id' => $snapshot->course));
    $completions = $DB->get_records_sql("SELECT u.id, u.firstname, u.lastname, DATE_FORMAT(FROM_UNIXTIME(cc.timecompleted),'%d-%m-%Y') as timecompleted
                                        FROM {local_ccr_crs_completions} cc JOIN {user} u ON u.id = cc.userid WHERE cc.snapshotid = ? ", array($id));
    $final_completions = array();

    foreach ($completions as $completion){
        $final_completions[] = array('firstname' => $completion->firstname, 'lastname' => $completion->lastname, 'timecompleted' => $completion->timecompleted);
    }

    $backup = new stdClass();
    $backup->snapshot = array('id' => $snapshot->id, 'courseid' => $course->id, 'course' => $course->fullname, 'notes' => $snapshot->notes, 'password' => $snapshot->password,
        'uses' => $snapshot->uses, 'datecreated' => $snapshot->timecreated, 'completions' => count($completions));
    $backup->completions = $final_completions;

    return $backup;
}

function save_snapshot($data = null) {
    global $DB, $USER;

    if ($data->id) {
        // Update existing record.
    } else {
        // Create a new entry in the snapshots table.
        $fullpassword = md5(uniqid(rand(), true));
        $info = new stdClass();
        $info->userid = $USER->id;
        $info->course = $data->course;
        $info->notes = $data->notes;
        $info->uses = 0;
        $info->timecreated = time();
        $info->firstrestoredate = null;
        $info->recentrestoredate = null;
        $info->password = substr($fullpassword,0,10);
        $snaphot = $DB->insert_record('local_ccr_snapshots', $info);
        if($snaphot) {
            // Get all the current users to snapshot completions.
            $completions = $DB->get_records('course_completions', array('course' => $data->course));
            foreach ($completions as $completion) {
                $course_completion = new stdClass();
                $course_completion->userid = $completion->userid;
                $course_completion->course = $completion->course;
                $course_completion->timeenrolled = $completion->timeenrolled;
                $course_completion->timestarted = $completion->timestarted;
                $course_completion->timecompleted = $completion->timecompleted;
                $course_completion->reaggregate = $completion->reaggregate;
                $course_completion->snapshotid = $snaphot;
                $DB->insert_record('local_ccr_crs_completions', $course_completion);
            }
            // Get all the current course completion criteria.
            $criteria_completions = $DB->get_records('course_completion_crit_compl', array('course' => $data->course));
            foreach ($criteria_completions as $completion) {
                $module_completion = new stdClass();
                $module_completion->userid = $completion->userid;
                $module_completion->course = $completion->course;
                $module_completion->criteriaid = $completion->criteriaid;
                $module_completion->gradefinal = $completion->gradefinal;
                $module_completion->unenroled = $completion->unenroled;
                $module_completion->timecompleted = $completion->timecompleted;
                $module_completion->snapshotid = $snaphot;
                $DB->insert_record('local_ccr_crit_compl', $module_completion);
            }
            // Get all the current course module completions.
            $module_completions = $DB->get_records_sql('SELECT cmc.* FROM {course_modules_completion} cmc JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid
                                  WHERE cm.course = ? ', array($data->course));
            foreach ($module_completions as $completion) {
                $module_completion = new stdClass();
                $module_completion->userid = $completion->userid;
                $module_completion->coursemoduleid = $completion->coursemoduleid;
                $module_completion->completionstate = $completion->completionstate;
                $module_completion->viewed = $completion->viewed;
                $module_completion->overrideby = $completion->overrideby;
                $module_completion->timemodified = $completion->timemodified;
                $module_completion->snapshotid = $snaphot;
                $DB->insert_record('local_ccr_crsmod_compl', $module_completion);
            }
        }
    }

    return $data->id;
}

function restore_snapshot($id){
    global $DB, $USER;

    // First lets get the completions for this snapshot.
    $completions = $DB->get_records('local_ccr_crs_completions', array('snapshotid' => $id));
    $crit_completions = $DB->get_records('local_ccr_crit_compl', array('snapshotid' => $id));
    $module_completions = $DB->get_records('local_ccr_crsmod_compl', array('snapshotid' => $id));

    $total_completions = 0;
    $total_criteria = 0;
    $total_mod_completion = 0;
    $failedcompletions = array();
    $failedcriterion = array();
    $failedmodules = array();
    // Restore the completions.
    foreach ($completions as $completion){
        // Check first if the completion record already exists first.
        $existing_completion = $DB->get_record('course_completions', array('course' => $completion->course, 'userid' => $completion->userid));
        if( $existing_completion ){
            $existing_completion->timeenrolled = $completion->timeenrolled;
            $existing_completion->timestarted = $completion->timestarted;
            $existing_completion->timecompleted = $completion->timecompleted;
            $existing_completion->reaggregate = $completion->reaggregate;
            if($DB->update_record('course_completions', $existing_completion)){
                $total_completions++;
            }else{
                $failedcompletions[] = $completion;
            }
        }else{
            $course_completion = new stdClass();
            $course_completion->userid = $completion->userid;
            $course_completion->course = $completion->course;
            $course_completion->timeenrolled = $completion->timeenrolled;
            $course_completion->timestarted = $completion->timestarted;
            $course_completion->timecompleted = $completion->timecompleted;
            $course_completion->reaggregate = $completion->reaggregate;
            if($DB->insert_record('course_completions', $course_completion)){
                $total_completions++;
            }else{
                $failedcompletions[] = $completion;
            }
        }
    }

    foreach ($crit_completions as $completion) {
        // Check first if the criteria completion already exists.
        $existing_completion = $DB->get_record('course_completion_crit_compl', array('course' => $completion->course, 'userid' => $completion->userid, 'criteriaid' => $completion->criteriaid));
        if( $existing_completion ){
            $existing_completion->userid = $completion->userid;
            $existing_completion->course = $completion->course;
            $existing_completion->criteriaid = $completion->criteriaid;
            $existing_completion->gradefinal = $completion->gradefinal;
            $existing_completion->unenroled = $completion->unenroled;
            $existing_completion->timecompleted = $completion->timecompleted;
            if( $DB->update_record('course_completion_crit_compl', $existing_completion) ){
                $total_criteria++;
            }else{
                $failedcriterion[] = $completion;
            }
        } else {
            $module_completion = new stdClass();
            $module_completion->userid = $completion->userid;
            $module_completion->course = $completion->course;
            $module_completion->criteriaid = $completion->criteriaid;
            $module_completion->gradefinal = $completion->gradefinal;
            $module_completion->unenroled = $completion->unenroled;
            $module_completion->timecompleted = $completion->timecompleted;
            if( $DB->insert_record('course_completion_crit_compl', $module_completion) ){
                $total_criteria++;
            }else{
                $failedcriterion[] = $completion;
            }
        }
    }

    foreach ($module_completions as $completion) {
        // Check first if the module completion already exists.
        $existing_completion = $DB->get_record('course_modules_completion', array('coursemoduleid' => $completion->coursemoduleid, 'userid' => $completion->userid));
        if( $existing_completion ){
            $existing_completion->coursemoduleid = $completion->coursemoduleid;
            $existing_completion->userid = $completion->userid;
            $existing_completion->completionstate = $completion->completionstate;
            $existing_completion->viewed = $completion->viewed;
            $existing_completion->overrideby = $completion->overrideby;
            $existing_completion->timemodified = $completion->timemodified;
            if( $DB->update_record('course_modules_completion', $existing_completion) ){
                $total_mod_completion++;
            }else{
                $failedmodules[] = $completion;
            }
        } else {
            $module_completion = new stdClass();
            $module_completion->userid = $completion->userid;
            $module_completion->coursemoduleid = $completion->coursemoduleid;
            $module_completion->completionstate = $completion->completionstate;
            $module_completion->viewed = $completion->viewed;
            $module_completion->overrideby = $completion->overrideby;
            $module_completion->timemodified = $completion->timemodified;
            if( $DB->insert_record('course_modules_completion', $module_completion) ){
                $total_mod_completion++;
            }else{
                $failedmodules[] = $completion;
            }
        }
    }

    $snapshot = $DB->get_record('local_ccr_snapshots', array('id' => $id));
    $failedjson['completions'] = json_encode($failedcompletions);
    $failedjson['criteria'] = json_encode($failedcriterion);
    $failedjson['modules'] = json_encode($failedmodules);
    $log = new stdClass();
    $log->userid = $USER->id;
    $log->course = $snapshot->course;
    $log->snapshotid = $id;
    $log->restoredcompletions = $total_completions;
    $log->restoredcriterion = $total_criteria;
    $log->restoredmodules = $total_mod_completion;
    $log->failedcompletions = count($completions) - $total_completions;
    $log->failedcriterion = count($crit_completions) - $total_criteria;
    $log->failedmodules = count($module_completions) - $total_mod_completion;
    $log->failedjson = $failedjson;

    $log->timecreated = time();
    $logid = $DB->insert_record('local_ccr_logs', $log);

    $details = array();
    $details['id'] = $logid;

    // Now update the uses.
    $snapshot->uses = $snapshot->uses + 1;
    if($snapshot->uses == 1){
        $snapshot->firstrestoredate = time();
    }else{
        $snapshot->recentrestoredate = time();
    }
    $DB->update_record('local_ccr_snapshots', $snapshot);

    return $details;
}

function get_restore_logs(){
    global $DB;

    $logs = $DB->get_records_sql("SELECT l.*, DATE_FORMAT(FROM_UNIXTIME(l.timecreated),'%d-%m-%Y') as timecreated, c.fullname as coursename,
                                        CONCAT(u.firstname, ' ', u.lastname) as username FROM {local_ccr_logs} l JOIN {course} c ON l.course = c.id JOIN {user} u ON u.id = l.userid");

    $final_logs = array();

    foreach ($logs as $log){
        $final_logs[] = array('id' => $log->id, 'coursename' => $log->coursename, 'username' => $log->username, 'timecreated' => $log->timecreated, 'restoredcompletions' => $log->restoredcompletions, 'restoredcriterion' => $log->restoredcriterion,
                                'restoredmodules' => $log->restoredmodules ,'failedcompletions' => $log->failedcompletions, 'failedcriterion' => $log->failedcriterion, 'failedmodules' => $log->failedmodules, 'snapshotid' => $log->snapshotid);
    }

    return $final_logs;
}
