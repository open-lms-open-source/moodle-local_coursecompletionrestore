<?php
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

namespace local_coursecompletionrestore\output;
defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
use renderable;
use context_system;
use moodle_url;
use tabobject;
use html_writer;

class renderer extends plugin_renderer_base{

    public function print_tabs($activePage = 'snapshots'){
        global $CFG;

        $snapshotsActive = false;
        $backupActive = false;
        $restoreActive = false;

        switch ($activePage) {
            case 'snapshots':
                $snapshotsActive = true;
                break;

            case 'backup':
                $backupActive = true;
                break;

            case 'restore':
                $restoreActive = true;
                break;
        }
        $context = [
            'snapshoturl' => $CFG->wwwroot . '/local/coursecompletionrestore/index.php',
            'backupurl' => $CFG->wwwroot . '/local/coursecompletionrestore/backup.php',
            'restoreurl' => $CFG->wwwroot . '/local/coursecompletionrestore/restore.php?action=logs',
            'snapshotsactive' => $snapshotsActive,
            'backupactive' => $backupActive,
            'restoreactive' => $restoreActive
        ];

        return $this->render_from_template('local_coursecompletionrestore/menu-bar', $context);
    }

    public function print_snapshots($renderable){
        global $CFG;

        $context = array(
            'pageurl' => $CFG->wwwroot . '/local/coursecompletionrestore/index.php',
            'snapshots' => $renderable->export_for_template($this)
        );
        return $this->render_from_template('local_coursecompletionrestore/completion-snapshots', $context);
    }

    public function print_backup_page($form){
        global $CFG;

        $context = array(
            'pageurl' => $CFG->wwwroot . '/local/coursecompletionrestore/backup.php',
            'formhtml' => $form,
            'title' => get_string('backup', 'local_coursecompletionrestore')
        );
        return $this->render_from_template('local_coursecompletionrestore/backup-page', $context);
    }

    public function print_snapshot_details($renderable){
        global $CFG;

        $object = $renderable->export_for_template($this);
        $button_link = $CFG->wwwroot . '/local/coursecompletionrestore/restore.php?action=confirm&id=' .$object['details']['id'];

        // Check if the user needs to enter a password to restore the snapshot.
        if( $object['details']['uses'] > 0){
            $button_link = $CFG->wwwroot . '/local/coursecompletionrestore/restore.php?action=password&id=' .$object['details']['id'];
        }
        $context = array(
            'pageurl' => $CFG->wwwroot . '/local/coursecompletionrestore/restore.php?action=view&id=' .$object['details']['id'],
            'snapshot' => $object['details'],
            'title' => $object['title'],
            'completions' => $object['completions'],
            'button_link' => $button_link
        );
        return $this->render_from_template('local_coursecompletionrestore/restore-snapshot', $context);
    }

    public function confirm_snapshot_restore($renderable){
        global $CFG;

        $object = $renderable->export_for_template($this);
        $context = array(
            'pageurl' => $CFG->wwwroot . '/local/coursecompletionrestore/restore.php?action=restore&snapshotid=' .$object['details']['id'],
            'snapshot' => $object['details'],
            'title' => $object['title'],
            'completions' => $object['completions'],
            'confirm_link' => $CFG->wwwroot . '/local/coursecompletionrestore/actions.php?action=restore&snapshotid=' .$object['details']['id'],
            'cancel_link' => $CFG->wwwroot . '/local/coursecompletionrestore/restore.php?action=view&id=' .$object['details']['id']
        );
        return $this->render_from_template('local_coursecompletionrestore/restore-confirm', $context);
    }

    public function restore_results($id){
        global $DB, $CFG;

        $restore = $DB->get_record('local_ccr_logs', array('id' => $id));
        $course = $DB->get_record('course', array('id' => $restore->course));
        $failures = $restore->failedjson;
        $failedcompletions = null;
        $failedcriteria = null;
        if(isset($failures['completions'])){
            $failedcompletions = json_decode($failures['completions']);
        }
        if(isset($failures['criteria'])) {
            $failedcriteria = json_decode($failures['criteria']);
        }
        $context = array(
            'title' => get_string('restore_details', 'local_coursecompletionrestore'),
            'snapshot' => $restore->snapshotid,
            'course' => $course->fullname,
            'success_completions' => $restore->restoredcompletions,
            'success_criteria' => $restore->restoredcriterion,
            'failed_completions' => $restore->failedcompletions,
            'failed_criteria' => $restore->failedcriterion,
            'completions_array' => $failedcompletions,
            'criteria_array' => $failedcriteria
        );
        return $this->render_from_template('local_coursecompletionrestore/restore-results', $context);
    }

    public function print_restore_logs($params){
        global $DB;

        $context = array(
            'title' => $params->title,
            'logs' => $params->logs,
        );
        return $this->render_from_template('local_coursecompletionrestore/restore-logs', $context);
    }

    public function input_password_restore($form){
        global $CFG;

        $context = array(
            'pageurl' => $CFG->wwwroot . '/local/coursecompletionrestore/restore.php',
            'formhtml' => $form,
            'title' => get_string('backup', 'local_coursecompletionrestore')
        );
        return $this->render_from_template('local_coursecompletionrestore/backup-page', $context);

    }
}