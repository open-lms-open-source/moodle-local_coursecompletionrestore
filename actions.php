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
 * eCommerce related management functions, this file needs to be included manually.
 *
 * @package    local_coursecompletionrestore
 * @copyright  2020 Lupiya Mujala <lupiya@ecreators.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/local/coursecompletionrestore/lib.php');

$id         = optional_param('snapshotid', 0, PARAM_INT);
$action     = optional_param('action', '', PARAM_TEXT);
$password     = optional_param('password', '', PARAM_TEXT);

require_login();

$context = context_system::instance();
$params = array('snapshotid'=>$id, 'action'=>$action);
$PAGE->set_url('/local/coursecompletionrestore/actions.php', $params);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);

if ($action == 'restore') {

    //Check if this is the  first restore or not.
    $snapshot = get_snaphot_details($id);
    if($snapshot->snapshot['uses'] > 0 && empty($password)){

        $params = array();
        $params['action'] = 'password';
        $params['id'] = $id;

        redirect(new \moodle_url('/local/coursecompletionrestore/confirm.php', $params));
    }
    $restore = restore_snapshot($id);

    $restore['action'] = 'results';

    redirect(new \moodle_url('/local/coursecompletionrestore/restore.php', $restore));
    exit;
}