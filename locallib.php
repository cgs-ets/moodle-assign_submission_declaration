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
      *  Get the settings for declartions submission plugin.
      *
      * @param MoodleQuickForm $mform The form to add elements to
      * @return void
      */
    public function get_settings(MoodleQuickForm $mform) {
        global $DB, $OUTPUT, $PAGE, $CFG;
        $declarationgroup = array();
        $defaultselect = 1;
        if ($this->get_config('declaration') == 0) {
            $defaultdeclaration = get_string('dummy_declaration', 'assignsubmission_declaration');
        } else {
            $id = $this->get_config('declaration');
            $sql = "SELECT *
                    FROM {assignsubmission_dec_details}
                    WHERE id = ?";
            $params = ['id' => $id];
            $defaultdeclaration = $DB->get_field_sql($sql, $params);
            $defaultselect = 1;
        }

        $d = new stdClass();
        $d->declaration_title = get_string('title', 'assignsubmission_declaration');
        $d->declaration_text  = get_string('dummy_declaration', 'assignsubmission_declaration');
        $mform->addElement('html',  $OUTPUT->render_from_template('assignsubmission_declaration/assignsubmission_declaration_template', $d));

         $value = get_string('dummy_declaration', 'assignsubmission_declaration');
        // $declarationgroup[] = $mform->createElement('textarea', 'assignsubmission_declaration_1', '' , /*'disabled="disabled*/'"  wrap="virtual" rows="5" cols="55" value="'. $value . '"placeholder ="' . $value . '"');
        // $declarationgroup[] = $mform->createElement('checkbox', 'assignsubmission_declaration_1_check', 'Select');

        // $mform->addGroup($declarationgroup, 'assignsubmission_declaration_group', get_string('title', 'assignsubmission_declaration'), ' ', false);
        // $mform->setDefault('assignsubmission_declaration_1', $defaultdeclaration);
        // $mform->setDefault('assignsubmission_declaration_1_check', $defaultselect);


        $attributes = ['assignmentid' => $this->assignment->get_instance()->id];
        $mform->addElement('text', 'declarationjson', get_string('declarationjson', 'assignsubmission_declaration'), $attributes); // Add elements to your form.
        $mform->setType('text', PARAM_RAW);                   // Set type of element.

        $datajson = new stdClass();
        $datajson->declaration_title = get_string('title', 'assignsubmission_declaration');
        $datajson->declaration_text = $value;
        $datajson->id = 1;
        $datajson->assignment = $this->assignment->get_instance()->id;
        $datajson->selected = $defaultselect;
        $declarationjson = [$datajson];

        $mform->setDefault('declarationjson', json_encode($declarationjson));        // Default value.
        $mform->hideIf('declarationjson', 'assignsubmission_declaration_enabled', 'notchecked');
        $mform->addElement('html',  $OUTPUT->render_from_template('assignsubmission_declaration/assignsubmission_declaration_add_new', ''));
        $PAGE->requires->js_call_amd('assignsubmission_declaration/assignsubmission_declaration', 'init');

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

        $declarations = json_decode($data->declarationjson);
        error_log(print_r($declarations, true));
        if (empty($data->assignsubmission_declaration_1_check)) {
            $this->set_error("ERROR!");
        } else {
            // Check if the text is already there.
            foreach($declarations as $declaration) {
                if(!isset($declaration->sqlid)) { // It's not in the DB.
                    $declaration->order = $declaration->id;
                    unset($declaration->id);
                }
            }

            $sql = "SELECT id
                    FROM {assignsubmission_dec_details}
                    WHERE declaration_text = ?";
            $params = ['declaration_text' => $data->assignsubmission_declaration_1];
            $id = $DB->get_field_sql($sql, $params);
            if (!$id) {
                $dataobject = new stdClass();
                $dataobject->assignment = $this->assignment->get_instance()->id;
                $dataobject->declaration_text = $data->assignsubmission_declaration_1;
                $dataobject->selected = 1;
                $id = $DB->insert_record('assignsubmission_dec_details', $dataobject, true);

                $this->set_config('declaration', $id);
                $this->set_config('declarationenabled', 1);

            }
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
        $submissionid = $submission ? $submission->id : 0;

        if (!isset($data->assignsubmission_declaration_1)) {
            $data->assignsubmission_declaration_1 = '';
        }
        // print_object($submission);
        if ($submission) {
            $declarationsubmission = $this->get_declaration_submission($submission->id);
            // var_dump($declarationsubmission);
            if ($declarationsubmission) {
                $data->assignsubmission_declaration_1 = $declarationsubmission->declaration_text;
                $mform->addElement('checkbox', 'declaration_text_cbox', "Declaration 1", $declarationsubmission->declaration_text);
                $mform->setDefault('declaration_text_cbox', $declarationsubmission->checked);
                $mform->addRule('declaration_text_cbox', null, 'required');
            } else {
                $declarationtext = $this->get_declaration_assessment();
                // var_dump($declarationtext); exit;
                $mform->addElement('checkbox', 'declaration_text_cbox', "Declaration 1", $declarationtext->declaration_text);
                $mform->addRule('declaration_text_cbox', null, 'required');
            }

        }

        return true;
    }

     /**
      * Get declaration submission information from the database
      *
      * @param  int $submissionid
      * @return mixed
      */
    private function get_declaration_submission($submissionid) {
        global $DB;
        $sql = "SELECT *
                FROM mdl_assignsubmission_declaration decl
                JOIN mdl_assignsubmission_dec_details det ON decl.detail = det.id
                where decl.submission = ?";
        $params = ['submission' => $submissionid];
        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Get the declaration details for this assessment.
     */
    private function get_declaration_assessment() {
        global $DB;
        $sql = "SELECT *
                FROM mdl_assignsubmission_dec_details
                WHERE assignment = ?";
        $params = ['assignment' => $this->assignment->get_instance()->id];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Save data to the database and trigger plagiarism plugin,
     * if enabled, to scan the uploaded content via events trigger
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB;

        $declarationsubmission = $this->get_declaration_submission($submission->id);

        $params = array(
        'context' => context_module::instance($this->assignment->get_course_module()->id),
        'courseid' => $this->assignment->get_course()->id,
        'objectid' => $submission->id,

        );
        if (!empty($submission->userid) && ($submission->userid != $USER->id)) {
            $params['relateduserid'] = $submission->userid;
        }
        if ($this->assignment->is_blind_marking()) {
            $params['anonymous'] = 1;
        }

        $groupname = null;
        $groupid = 0;
        // Get the group name as other fields are not transcribed in the logs and this information is important.
        if (empty($submission->userid) && !empty($submission->groupid)) {
            $groupname = $DB->get_field('groups', 'name', array('id' => $submission->groupid), MUST_EXIST);
            $groupid = $submission->groupid;
        } else {
            $params['relateduserid'] = $submission->userid;
        }

          // Unset the objectid and other field from params for use in submission events.
          unset($params['objectid']);
          $params['other'] = array(
          'submissionid' => $submission->id,
          'submissionattempt' => $submission->attemptnumber,
          'submissionstatus' => $submission->status,
        // 'onlinetextwordcount' => $count,
          'groupid' => $groupid,
          'groupname' => $groupname
          );

          if ($declarationsubmission) {
              $declarationsubmission->select = $data->declaration_text_cbox;
              $params['objectid'] = $declarationsubmission->id;
              $updatestatus = $DB->update_record('assignsubmission_declaration', $declarationsubmission);
              return  $updatestatus;
          } else {
              $declarationsubmission = new stdClass();
              $declarationsubmission->assignment = $this->assignment->get_instance()->id;
              $declarationsubmission->submission = $submission->id;
              $declarationsubmission->detail = $this->get_declaration_assessment()->id;
              $declarationsubmission->checked = $data->declaration_text_cbox;

              $declarationsubmission->id = $DB->insert_record('assignsubmission_declaration', $declarationsubmission);
              $params['objectid'] = $declarationsubmission->id;
              return $declarationsubmission->id > 0;
          }

    }

      /**
       * No tick is set for this submission
       *
       * @param stdClass $submission
       * @return bool
       */
    public function is_empty(stdClass $submission) {
        $descriptionsubmission = $this->get_declaration_submission($submission->id);
        $selected = 0;
        if ($descriptionsubmission) {
            $selected = $descriptionsubmission->checked == 1 ? 0 : 1;
        }
        return $selected;
    }
     /**
      * Determine if a submission is empty
      *
      * This is distinct from is_empty in that it is intended to be used to
      * determine if a submission made before saving is empty.
      *
      * @param stdClass $data The submission data
      * @return bool
      */
    public function submission_is_empty(stdClass $data) {
        if (!isset($data->declaration_text_cbox)) {
            return true;
        }
        if (isset($data->declaration_text_cbox)) {
            return false;
        }

    }


    /**
     * Display a ✔ in the submission status table
     *
     * @param stdClass $submission
     * @param bool $showviewlink - If the summary has been truncated set this to true
     * @return string
     */
    public function view_summary(stdClass $submission, & $showviewlink) {
        $declarationsubmission = $this->get_declaration_submission($submission->id);
        $o = '';
        if ($declarationsubmission) {
              $o = $this->assignment->get_renderer()->container('✔', 'descriptorcontainer');
        }

        return $o;
    }
    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;

        // Will throw exception on failure.
        $DB->delete_records('assignsubmission_declaration', array('assignment' => $this->assignment->get_instance()->id));

        return true;
    }

    /**
     * Remove a submission.
     *
     * @param stdClass $submission The submission
     * @return boolean
     */
    public function remove($submission) {
        global $DB;
        $submissionid = $submission ? $submission->id : 0;
        if ($submissionid) {
            $DB->delete_records('assignsubmission_declaration', array('submission' => $submissionid));
        }
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
