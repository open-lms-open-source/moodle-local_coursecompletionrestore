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
 *
 *
 * @package    local_coursecompletionrestore
 * @copyright  2020 Lupiya Mujala <lupiya@ecreators.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/local/coursecompletionrestore/lib.php');
require($CFG->dirroot . '/local/coursecompletionrestore/classes/output/enter_password_form.php');

require_login();

use local_coursecompletionrestore\output;

$snapshotid = optional_param('id', 0, PARAM_INT);
$action = optional_param('action' , 'logs', PARAM_TEXT);

$context = context_system::instance();

$title = get_string('restore', 'local_coursecompletionrestore');
$params = array('id'=>$snapshotid, 'action'=>$action);
$PAGE->set_url('/local/coursecompletionrestore/restore.php', $params);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
$PAGE->requires->css('/local/coursecompletionrestore/assets/css/style.css');

$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$renderer = $PAGE->get_renderer('local_coursecompletionrestore');
$params = new stdClass();
$params->title = $title;

echo $OUTPUT->header();

echo $renderer->print_tabs('restore');

if($action == 'view') {
    if($snapshotid){
        $snapshot = get_snaphot_details($snapshotid);
    }
    $params->snapshot = $snapshot->snapshot;
    $params->completions = $snapshot->completions;
    $renderable = new output\restore_page($params);
    echo $renderer->print_snapshot_details($renderable);
}elseif ($action == 'confirm') {
    if($snapshotid){
        $snapshot = get_snaphot_details($snapshotid);
    }
    $params->title = get_string('confirm_restore', 'local_coursecompletionrestore');
    $params->snapshot = $snapshot->snapshot;
    $params->completions = $snapshot->completions;
    $renderable = new output\restore_page($params);
    echo $renderer->confirm_snapshot_restore($renderable);
}elseif ($action == 'results') {
    echo $renderer->restore_results($snapshotid);
}elseif ($action == 'logs') {
    $params->title = get_string('restore_logs', 'local_coursecompletionrestore');
    $logs = get_restore_logs();
    $params->logs = $logs;

    echo $renderer->print_restore_logs($params);
}

echo $OUTPUT->footer();