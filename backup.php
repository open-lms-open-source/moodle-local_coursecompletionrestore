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
 * @package    local_ecommerce
 * @copyright  2017 SEBALE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->dirroot . '/local/coursecompletionrestore/lib.php');
require($CFG->dirroot . '/local/coursecompletionrestore/classes/output/add_completion_snapshot_form.php');

require_login();

$context = context_system::instance();

$title = get_string('resetmycourse', 'local_coursecompletionrestore');
$PAGE->set_url('/local/coursecompletionrestore/index.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);

$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$renderer = $PAGE->get_renderer('local_coursecompletionrestore');
$courses = get_course_list();
$returnurl = new moodle_url($CFG->wwwroot . '/local/coursecompletionrestore/index.php');

$editform = new add_completion_snapshot_form(null, array('courses' => $courses, 'data' => array()));

if ($editform->is_cancelled()) {

    redirect($returnurl);

} else if ($data = $editform->get_data()) {

    $data->id = save_snapshot($data);

    redirect($returnurl);
}

echo $OUTPUT->header();

echo $renderer->print_tabs('backup');
echo $renderer->print_backup_page($editform->export_for_template($renderer));

echo $OUTPUT->footer();