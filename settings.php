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
 * Link to plugin generator.
 *
 * @package    local_coursecompletionrestore
 * @copyright  2020 Lupiya Mujala <lupiya@ecreators.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    //section to show the plugin name add settings in moodle
    $settings = new admin_settingpage('local_coursecompletionrestore', new lang_string('pluginname', 'local_coursecompletionrestore'));
    $ADMIN->add('localplugins', $settings);

    //section to add the checkbox
    $name = 'local_coursecompletionrestore/send_email_reminder';
    $visiblename = get_string('send_email_reminder','local_coursecompletionrestore');
    $description = get_string('send_email_reminder_desc','local_coursecompletionrestore');
    $settings->add(new admin_setting_configcheckbox($name, $visiblename, $description, 0));

    $name = 'local_coursecompletionrestore/email_body';
    $visiblename = get_string('email_body','local_coursecompletionrestore');
    $description = get_string('email_body_desc','local_coursecompletionrestore');
    $settings->add(new admin_setting_configtextarea($name, $visiblename, $description, get_string('snapshot_restore_email', 'local_coursecompletionrestore')));

    $name = 'local_coursecompletionrestore/email_timing';
    $visiblename = get_string('email_timing','local_coursecompletionrestore');
    $description = get_string('email_timing_desc','local_coursecompletionrestore');
    $settings->add(new admin_setting_configtext($name, $visiblename, $description, 86400));

}

