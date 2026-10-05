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
 * Upgrade file.
 *
 * @package    mod_qrcodecheck
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade steps for qrcodecheck.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_qrcodecheck_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2026100501) {
        // QR scan is the only automatic completion rule supported by this activity.
        // Repair instances left in an impossible state by older versions:
        // automatic completion enabled in course_modules while completionscan is disabled.
        $sql = "SELECT DISTINCT q.id
                  FROM {qrcodecheck} q
                  JOIN {course_modules} cm
                    ON cm.instance = q.id
                  JOIN {modules} m
                    ON m.id = cm.module
                 WHERE m.name = :modname
                   AND cm.completion = :completion
                   AND cm.deletioninprogress = 0
                   AND q.completionscan = 0";
        $instanceids = $DB->get_fieldset_sql($sql, [
            "modname" => "qrcodecheck",
            "completion" => COMPLETION_TRACKING_AUTOMATIC,
        ]);

        if ($instanceids) {
            [$insql, $params] = $DB->get_in_or_equal($instanceids, SQL_PARAMS_NAMED, "instance");
            $DB->set_field_select("qrcodecheck", "completionscan", 1, "id {$insql}", $params);
        }

        upgrade_mod_savepoint(true, 2026100501, "qrcodecheck");
    }

    return true;
}
