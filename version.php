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
 * This plugin provides the ability to backup and restore course completions after unlocking.
 *
 *
 * @package    local_coursecompletionrestore
 * @copyright  2020 eCreators
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    None
 */

$plugin->version  = 2020041501;
$plugin->requires = 2017111300;
$plugin->release = '1.0';
$plugin->maturity = MATURITY_STABLE;
$plugin->component = 'local_coursecompletionrestore';
