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

        if (!$this->get_config('declaration')) {
            $d = $this->get_template_context_for_setting_form();
            $declarations['declarations'][] = $d;
            $declarationjson = [$d];
        } else {
            $declarations['declarations'] = $this->get_template_context_for_setting_form(0);
            $declarationjson = $declarations['declarations'];

        }

        $mform->addElement('html', $OUTPUT->render_from_template('assignsubmission_declaration/assignsubmission_declaration_template', $declarations));
        $attributes = ['assignmentid' => 1];
        $mform->addElement('text', 'declarationjson', get_string('declarationjson', 'assignsubmission_declaration'), $attributes); // Add elements to your form.
        $mform->setType('declarationjson', PARAM_RAW);   // Set type of element.
        $mform->setDefault('declarationjson', json_encode($declarationjson));        // Default value.
        $mform->hideIf('declarationjson', 'assignsubmission_declaration_enabled', 'notchecked');
        $mform->addElement('html',  $OUTPUT->render_from_template('assignsubmission_declaration/assignsubmission_declaration_add_new', ''));
        $mform->hideIf('assignsubmission_declaration_group', 'assignsubmission_declaration_enabled', 'notchecked');
        $PAGE->requires->js_call_amd('assignsubmission_declaration/assignsubmission_declaration', 'init');

    }

    /**
     * Get the data to populate setting form template.
     */
    private function get_template_context_for_setting_form($new = 1) {
        global $DB;

        if ($new) {

            $d = new stdClass();
            $d->declaration_title = get_string('title', 'assignsubmission_declaration');
            $d->declaration_text  = get_string('dummy_declaration', 'assignsubmission_declaration');
            $d->selected = 1;
            $d->id = 1;
            $d->ordered = 1;
            $d->assignment = 0;
            return $d;
        } else {
            $ids = $this->get_config('declaration');

            $sql = "SELECT *
                    FROM {assignsubmission_dec_details}
                    WHERE id IN ($ids)";

            $results = $DB->get_records_sql($sql);
            foreach ($results as $result) {
                $result->sqlid = $result->id;
                $result->id = $result->ordered;
            }

            return array_values($results);
        }
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

        if (empty($data->declarationjson)) {
            $this->set_error("ERROR!");
        } else {
            $dbids = [];
            foreach ($declarations as $declaration) {
                if (!isset($declaration->sqlid)) { // It's not in the DB.
                    $declaration->ordered = $declaration->id;
                    $declaration->assignment = $this->assignment->get_instance()->id;
                    unset($declaration->id);
                    $dbids[] = $DB->insert_record('assignsubmission_dec_details', $declaration, true);
                } else {
                    // Update text.
                    $dbids[] = $declaration->sqlid;
                    $declaration->id = $declaration->sqlid;
                    unset($declaration->sqlid);
                    $DB->update_record('assignsubmission_dec_details', $declaration);
                }
            }
            $dbids = array_unique($dbids);
            $dbids = implode(',', $dbids);
            $this->set_config('declaration', $dbids);
            $this->set_config('declarationenabled', 1);

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
        global $OUTPUT, $PAGE;

        list($declarations, $decdetails) = $this->get_template_context_for_student_view();

        if (!isset($data->assignsubmission_declaration_1)) {
            $data->assignsubmission_declaration_1 = '';
        }

        if ($submission) {
            $declarationsubmission = $this->get_declaration_submission($submission->id);

            if ($declarationsubmission) {
                list($declarations, $decdetails) = $this->get_template_context_for_student_view(1, $submission);
                $mform->addElement('html', $OUTPUT->render_from_template('assignsubmission_declaration/assignsubmission_declaration_student_view', $declarations));
            } else {
                $mform->addElement('html', $OUTPUT->render_from_template('assignsubmission_declaration/assignsubmission_declaration_student_view', $declarations));
            }

        }

        $mform->addElement('text', 'declarationjson', get_string('declarationjson', 'assignsubmission_declaration')); // Add elements to your form.
        $mform->setType('declarationjson', PARAM_RAW);   // Set type of element.
        $mform->setDefault('declarationjson', json_encode($decdetails));
        $PAGE->requires->js_call_amd('assignsubmission_declaration/assignsubmission_declaration_student_submit', 'init');

        return true;
    }

    private function get_template_context_for_student_view($withsubmission = 0, $submission = null) {
        $decdetails = [];
        $declarations = $this->get_declaration_assessment();
        if ($withsubmission == 0) {
            foreach ($declarations as $declaration) {
                    $dec = new stdClass();
                    $dec->detail = $declaration->id; // In the DB the detail column is the id of mdl_assignsubmission_declaration table.
                    $dec->assignment = $declaration->assignment;
                    $dec->submission = 0;
                    $dec->selected = 0;
                    $declaration->selected = 0; // The one that comes from the DB is the selected when setting the submission.
                    $decdetails[] = $dec;
            }

            $declarations['declarations'] = array_values($declarations);

        } else {
            // Check if there are new declarations added after the first time.
            $alldeclarations = $this->get_declaration_assessment();
            $declarations = $this->get_declaration_submission($submission->id);
            $currentids = array_keys($declarations);
            $allids = array_keys($alldeclarations);
            $missingids = [];

            foreach ($allids as $id) {
                if (!in_array($id, $currentids)) {
                    $missingids[] = $id;
                }

            }

            foreach ($declarations as $declaration) {

                    $dec = new stdClass();
                    $dec->detail = $declaration->id; // In the DB the detail column is the id of mdl_assignsubmission_declaration table.
                    $dec->assignment = $declaration->assignment;
                    $dec->submission = $declaration->id;
                    $dec->checked = $declaration->selected;
                    $decdetails[] = $dec;

            }
            $declarations['declarations'] = array_values($declarations);
        }

        return [$declarations, $decdetails];

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

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get the declaration details for this assessment.
     */
    private function get_declaration_assessment() {
        global $DB;
        $sql = "SELECT *
                FROM mdl_assignsubmission_dec_details
                WHERE assignment = ? AND selected = ? ";
        $params = ['assignment' => $this->assignment->get_instance()->id, 'selected' => 1];

        return $DB->get_records_sql($sql, $params);
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
          'groupid' => $groupid,
          'groupname' => $groupname
          );

          if ($declarationsubmission) {
              $declarationsubmission->select = $data->declaration_text_cbox;
              $params['objectid'] = $declarationsubmission->id;
              $updatestatus = $DB->update_record('assignsubmission_declaration', $declarationsubmission);
              return  $updatestatus;
          } else {
              $submmited = json_decode($data->declarationjson);
              foreach ($submmited as $sub) {

                  $declarationsubmission = new stdClass();
                  $declarationsubmission->assignment = $sub->assignment;
                  $declarationsubmission->submission = $submission->id;
                  $declarationsubmission->detail = $sub->detail;
                  $declarationsubmission->checked = $sub->selected;
                  $declarationsubmission->id = $DB->insert_record('assignsubmission_declaration', $declarationsubmission);
              }

              $params['objectid'] = implode(',', $declarationsubmission->id);
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

        foreach ($descriptionsubmission as $submitted) {
            if ($submitted->selected) {
                $selected++;
            }
        }

        return $selected < 0;
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

        $submitteddeclarations = json_decode($data->declarationjson);
        $selected = 0;
        foreach ($submitteddeclarations as $submitteddec) {
            if ($submitteddec->selected == 1) {
                $selected++;
            }
        }

        if ($selected < count($submitteddeclarations)) {
            return true;
        }
        if ($selected == count($submitteddeclarations)) {
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
        $str = '';
        foreach ($declarationsubmission as $ds) {

            $str .= "$ds->declaration_title ✔ <br>";

        }
        $o = '';
        if ($declarationsubmission) {
              $o = $this->assignment->get_renderer()->container($str, 'descriptorcontainer');
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
        $t = $DB->delete_records('assignsubmission_dec_details', array('assignment' => $this->assignment->get_instance()->id));
        $t1 = $DB->delete_records('assignsubmission_declaration', array('assignment' => $this->assignment->get_instance()->id));
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
