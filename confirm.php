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

$returnurl = new moodle_url($CFG->wwwroot . '/local/coursecompletionrestore/index.php');

if($snapshotid){
    $snapshot = get_snaphot_details($snapshotid);
}
$editform = new enter_password_form(null, array('password' => $snapshot->snapshot['password'], 'data' => $snapshot->snapshot));

if ($editform->is_cancelled()) {

    redirect($returnurl);

} else if ($data = $editform->get_data()) {
    $restore_url = $CFG->wwwroot . '/local/coursecompletionrestore/actions.php?action=restore&snapshotid=' .$snapshotid .'&password=' .$data->enter_password;
    redirect($restore_url);
}

echo $renderer->input_password_restore($editform->export_for_template($renderer));

echo $OUTPUT->footer();