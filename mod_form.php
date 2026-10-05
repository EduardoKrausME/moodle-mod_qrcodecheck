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
 * Activity settings form.
 *
 * @package   mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("{$CFG->dirroot}/course/moodleform_mod.php");

/**
 * QR Code Check activity form.
 */
class mod_qrcodecheck_mod_form extends moodleform_mod {
    /**
     * Defines activity fields.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement("header", "general", get_string("general", "form"));
        $mform->addElement("text", "name", get_string("name"), ["size" => 64]);
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", null, "required", null, "client");

        $this->standard_intro_elements();
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Adds custom completion rules.
     *
     * @return array
     */
    public function add_completion_rules() {
        $mform = $this->_form;
        $suffix = $this->get_suffix();
        $field = "completionscan{$suffix}";
        $mform->addElement(
            "checkbox",
            $field,
            "",
            get_string("completionscan", "qrcodecheck")
        );
        $mform->addHelpButton($field, "completionscan", "qrcodecheck");
        $mform->setDefault($field, 1);
        return [$field];
    }

    /**
     * Ensures an unticked completion checkbox is persisted as zero.
     *
     * @param stdClass $data Form data.
     * @return void
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        if (!empty($data->completionunlocked)) {
            $field = "completionscan" . $this->get_suffix();
            if (empty($data->{$field})) {
                $data->{$field} = 0;
            }
        }
    }

    /**
     * Checks whether custom completion is enabled.
     *
     * @param array $data Form data.
     * @return bool
     */
    public function completion_rule_enabled($data) {
        $field = "completionscan" . $this->get_suffix();
        return !empty($data[$field]);
    }
}
