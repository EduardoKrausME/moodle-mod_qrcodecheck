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
 * Restore structure step.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qrcodecheck_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines restore paths.
     *
     * @return array
     */
    protected function define_structure() {
        $paths = [new restore_path_element("qrcodecheck", "/activity/qrcodecheck")];
        if ($this->get_setting_value("userinfo")) {
            $paths[] = new restore_path_element("qrcodecheck_session", "/activity/qrcodecheck/sessions/session");
            $paths[] = new restore_path_element("qrcodecheck_record", "/activity/qrcodecheck/sessions/session/records/record");
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restores activity instance.
     *
     * @param array $data Data.
     * @return void
     */
    protected function process_qrcodecheck($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $newid = $DB->insert_record("qrcodecheck", $data);
        $this->apply_activity_instance($newid);
    }

    /**
     * Restores a historical session as closed.
     *
     * @param array $data Data.
     * @return void
     */
    protected function process_qrcodecheck_session($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->qrcodecheckid = $this->get_new_parentid("qrcodecheck");
        $data->startedby = $this->get_mappingid("user", $data->startedby);
        $data->endedat = $data->endedat ?: $data->startedat;
        $data->secret = bin2hex(random_bytes(32));
        $newid = $DB->insert_record("qrcodecheck_sessions", $data);
        $this->set_mapping("qrcodecheck_session", $oldid, $newid);
    }

    /**
     * Restores an attendance record.
     *
     * @param array $data Data.
     * @return void
     */
    protected function process_qrcodecheck_record($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->qrcodecheckid = $this->get_new_parentid("qrcodecheck");
        $data->sessionid = $this->get_new_parentid("qrcodecheck_session");
        $data->userid = $this->get_mappingid("user", $data->userid);
        $newid = $DB->insert_record("qrcodecheck_records", $data);
        $this->set_mapping("qrcodecheck_record", $oldid, $newid);
    }

    /**
     * Restores files.
     *
     * @return void
     */
    protected function after_execute() {
        $this->add_related_files("mod_qrcodecheck", "intro", null);
    }
}
