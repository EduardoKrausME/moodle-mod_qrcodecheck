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
 * Backup structure step.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_qrcodecheck_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines backup structure.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value("userinfo");

        $qrcodecheck = new backup_nested_element("qrcodecheck", ["id"], [
            "name", "intro", "introformat", "completionscan", "timecreated", "timemodified",
        ]);
        $sessions = new backup_nested_element("sessions");
        $session = new backup_nested_element("session", ["id"], [
            "startedby", "startedat", "endedat", "projectorip", "timecreated",
        ]);
        $records = new backup_nested_element("records");
        $record = new backup_nested_element("record", ["id"], [
            "userid", "scannedat", "ipaddress", "projectorip", "ipmatch", "timecreated", "timemodified",
        ]);

        $qrcodecheck->add_child($sessions);
        $sessions->add_child($session);
        $session->add_child($records);
        $records->add_child($record);

        $qrcodecheck->set_source_table("qrcodecheck", ["id" => backup::VAR_ACTIVITYID]);
        if ($userinfo) {
            $session->set_source_table("qrcodecheck_sessions", ["qrcodecheckid" => backup::VAR_PARENTID]);
            $record->set_source_table("qrcodecheck_records", ["sessionid" => backup::VAR_PARENTID]);
        }

        $session->annotate_ids("user", "startedby");
        $record->annotate_ids("user", "userid");
        $qrcodecheck->annotate_files("mod_qrcodecheck", "intro", null);

        return $this->prepare_activity_structure($qrcodecheck);
    }
}
