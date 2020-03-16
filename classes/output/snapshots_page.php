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
 * @category    string
 * @copyright   2020 Lupiya Mujala <lupiya@ecreators.com.au>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursecompletionrestore\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class snapshots_page implements renderable, templatable {

    private $title;
    private $array;

    public function __construct($params) {
        $this->title = $params->title;
        $this->array = $params->array;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * $type string
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        return array(
            'title' => $this->title,
            'array' => $this->array
        );
    }

}