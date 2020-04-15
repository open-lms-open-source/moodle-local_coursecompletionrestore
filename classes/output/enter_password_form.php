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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->libdir . '/form/autocomplete.php');


class enter_password_form extends moodleform{

    public function definition() {

        $mform = $this->_form;
        $password = $this->_customdata['password'];
        $data = $this->_customdata['data'];
        $required = get_string('required');

        $params = new stdClass();
        $params->password = $password;

        $mform->addElement('text', 'enter_password', get_string('password_enrty', 'local_coursecompletionrestore', $params));
        $mform->addRule('enter_password', $required, 'required', null, 'client');
        $mform->addHelpButton('enter_password', 'password', 'local_coursecompletionrestore', 'password');
        $mform->setType('enter_password', PARAM_TEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();

        $this->set_data($data);
    }

    public function validation($data, $files) {
        global $DB;
        $errors = array();

        if(!$DB->get_record('local_ccr_snapshots', array('id' => $data['id'], 'password' => $data['enter_password']))){
            $errors['password']  = "The password entered didn't match the database entry for this snapshot";
            echo "<div class='error'>The password entered didn't match the database entry for this snapshot</div>";
        }
        return $errors;
    }


    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();

        return $formhtml;
    }

}