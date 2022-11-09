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
 * This file contains the definition for the library class for declaration submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package assignsubmission_declarations
 * @copyright 2022 Veronica Bermegui
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class assign_submission_declaration extends assign_submission_plugin {

    public function get_name() {
        return get_string('declaration', 'assignsubmission_declaration');
    }

     /**
      *  Get the settings for declartions submission plugin
      *
      * @param MoodleQuickForm $mform The form to add elements to
      * @return void
      */
    public function get_settings(MoodleQuickForm $mform) {
        $declarationgroup = array();
        $declarationgroup2 = array();
        $value = get_string('dummy_declaration', 'assignsubmission_declaration');

        $declarationgroup[] = $mform->createElement('textarea', 'assignsubmission_declaration_d1', 'hello' , 'disabled="disabled"  wrap="virtual" rows="5" cols="55" value="'. $value . '"placeholder ="' . $value . '"');
        $declarationgroup[] = $mform->createElement('checkbox', 'assignsubmission_declaration_d1_check', 'Select');

        $mform->addGroup($declarationgroup, 'assignsubmission_declaration_group', 'Declaration 1', ' ', false);
        $mform->hideIf('assignsubmission_declaration_group', 'assignsubmission_declaration_enabled', 'notchecked');

    }

     /**
      * Save the settings for declaration submission plugin
      *
      * @param stdClass $data
      * @return bool
      */
    public function save_settings(stdClass $data) {
        global $DB;
        if (empty($data->assignsubmission_declarations_d1_check)) {
            $this->set_error("ERROR!");
        } else {
            $dataobject = new stdClass();
            $dataobject->assignment = $this->assignment->get_instance()->id;
            $dataobject->declaration_text = $data->assignsubmission_declaration_d1;
            $dataobject->selected = 1;
            $declaration = $DB->insert_record('assignsubmission_dec_details', $dataobject);


            return true;
        }
    }

    /**
     * Add form elements for settings
     *
     * @param mixed $submission can be null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return true if elements were added to the form
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        $elements = array();

        return true;
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of settings
     * @since Moodle 3.2
     */
    public function get_config_for_external() {
        return (array) $this->get_config();
    }

}
