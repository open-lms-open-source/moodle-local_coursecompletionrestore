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
 * @package   local_coursecompletionrestore
 * @category  task
 * @copyright 2020 eCreators
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursecompletionrestore\task;

/**
 * Task to process coursecompletionrestore send email reminder.
 *
 * @copyright  2020 eCreators
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_email_reminder extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('send_email_reminder', 'local_coursecompletionrestore');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $DB;

        if (!get_config('local_coursecompletionrestore', 'send_email_reminder')) {
            return;
        }

        mtrace('Started sending email reminders to re-apply snapshot');

        $config = get_config('local_coursecompletionrestore');

        // Get all the snapshots not yet re-applied.
        $snapshots = $DB->get_records('local_ccr_snapshots', array('uses' => 0));
        foreach ($snapshots as $snapshot){

            //Check if it has been at least a day since the snapshot was made.
            $diff = abs($snapshot->timecreated - time());
            if($diff > $config->email_timing){
                // Check if an email reminder has been sent today.
                $recent_log = $DB->get_record_sql('SELECT * FROM {local_ccr_email_reminders} WHERE userid = ? AND snapshotid =? ORDER BY timecreated DESC LIMIT 1', array($snapshot->userid, $snapshot->id));
                $timediff = 0;
                if($recent_log) {
                    $timediff = abs($recent_log->timecreated - time());
                }

                // Send the email if it has been more than a day.
                if($timediff > $config->email_timing || $timediff == 0){

                    $userTo = $DB->get_record('user', array('id'=>$snapshot->userid));
                    $userFrom = \core_user::get_support_user();

                    $a = new \stdClass();
                    $a->snapshot = $snapshot->id;
                    $a->firstname = $userTo->firstname;

                    $message = "";

                    $config_message = get_config('local_coursecompletionrestore', 'email_body');

                    if (!empty($config_message)) {
                        foreach ($a as $name => $value) {
                            $config_message = str_replace('{$a->' . $name .'}', $value, $config_message);
                        }
                        $message = nl2br($config_message);
                    } else {
                        $message = nl2br(get_string('snapshot_restore_email', 'local_coursecompletionrestore', $a));
                    }

                    $plaintext = format_text_email($message, FORMAT_HTML);

                    // Subject
                    $subject = get_string('snapshot_restore_email_subject', 'local_coursecompletionrestore' );

                    $eventdata = new \core\message\message();
                    $eventdata->userfrom         = $userFrom;
                    $eventdata->userto           = $userTo;
                    $eventdata->subject          = $subject;
                    $eventdata->fullmessage      = $plaintext;
                    $eventdata->fullmessageformat = FORMAT_HTML;
                    $eventdata->fullmessagehtml  = $message;
                    $eventdata->smallmessage     = '';
                    $eventdata->notification     = 1;
                    $eventdata->courseid = SITEID;

                    // Required for messaging framework
                    $eventdata->component = 'local_coursecompletionrestore';
                    $eventdata->name = 'snapshot_resore_reminder';

                    if(message_send($eventdata)){
                        $email = new \stdClass();
                        $email->userid = $snapshot->userid;
                        $email->snapshotid = $snapshot->id;
                        $email->content = $message;
                        $email->timecreated = time();
                        $DB->insert_record('local_ccr_email_reminders', $email);
                        mtrace('Email reminder sent to user '. $userTo->firstname . ' for snapshot ' . $snapshot->id);
                    }

                }
            }
        }

        mtrace('Finished sending email reminders to re-apply snapshot');
    }
}
