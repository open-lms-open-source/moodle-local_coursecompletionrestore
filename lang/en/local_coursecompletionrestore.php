<?php
// This file is part of the Local plans plugin
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
 * This plugin allows course completions to backed up
 * before making modifications to the course completion
 * settings.
 *
 * @package    local
 * @subpackage ecommerce
 * @copyright  2017
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Course Completion Backup and Restore';
$string['pluginname_desc'] = 'This plugin provides the ability to backup and restore course completion so that course completions can be unlocked.';
$string['resetmycourse'] = 'Course Completion Backup and Restore';
$string['backup'] = 'Backup';
$string['backupcourse'] = 'Backup Course Completions';
$string['restore'] = 'Restore';
$string['snapshot'] = 'Snapshot';
$string['snapshots'] = 'Snapshots';
$string['course'] = 'Course Name';
$string['notes'] = 'Notes';
$string['submit'] = 'Submit';
$string['actions'] = 'Actions';
$string['timecreated'] = 'Date Created';
$string['uses'] = 'Uses';
$string['completions'] = 'Completions';
$string['firstname'] = 'First Name';
$string['lastname'] = 'Lastname';
$string['timecompleted'] = 'Completion Date';
$string['confirm'] = 'Confirm';
$string['cancel'] = 'Cancel';
$string['restore_details'] = 'Restore Details';
$string['completions_restored'] = 'Completions Restored';
$string['criteria_restored'] = 'Criteria Completions Restored';
$string['modules_restored'] = 'Module Completions Restored';
$string['failed_completions'] = 'Failed Completions to Restore';
$string['failed_criteria'] = 'Failed Criteria Completions to Restore';
$string['failed_modules'] = 'Failed Module Completions to Restore';
$string['username'] = 'Username';
$string['restored_by'] = 'Restored By';
$string['restore_logs'] = 'Restore Logs';
$string['confirm_restore'] = 'Confirm Restoration';
$string['date'] = 'Date';
$string['password_enrty'] = 'Please enter the password {$a->password}';
$string['firstuse'] = 'First Restore';
$string['lastuse'] = 'Recent Restore';
$string['send_email_reminder'] = 'Send Email Reminder';
$string['send_email_reminder_desc'] = "Send Email Reminder to users when snapshots haven't been reapplied";
$string['email_body'] = 'Email Body';
$string['email_body_desc'] = 'This is the email body that will be sent in the reminder email';
$string['email_timing'] = 'Email Sending Frequency';
$string['email_timing_desc'] = 'This is the frequency the email will be sent in seconds';
$string['snapshot_restore_email'] = 'Dear {$a->firstname}, 

                                    The snapshot with ID {$a->snapshot} has not yet been reapplied. Please ensure that this is applied once changes to the course are completed.';
$string['snapshot_restore_email_subject'] = "Course Snapshot Not Re-Applied";