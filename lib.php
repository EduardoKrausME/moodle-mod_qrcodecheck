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
 * Required activity callbacks.
 *
 * @package   mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_qrcodecheck\manager;

/**
 * Returns supported Moodle features.
 *
 * @param string $feature Feature constant.
 * @return bool|null
 */
function qrcodecheck_supports($feature) {
    return match ($feature) {
        FEATURE_MOD_INTRO => true,
        FEATURE_SHOW_DESCRIPTION => true,
        FEATURE_COMPLETION_HAS_RULES => true,
        FEATURE_BACKUP_MOODLE2 => true,
        FEATURE_GROUPS => false,
        FEATURE_GROUPINGS => false,
        default => null,
    };
}

/**
 * Creates an activity instance.
 *
 * @param stdClass $data Form data.
 * @param mod_qrcodecheck_mod_form|null $mform Form.
 * @return int
 */
function qrcodecheck_add_instance($data, $mform = null) {
    return manager::create($data);
}

/**
 * Updates an activity instance.
 *
 * @param stdClass $data Form data.
 * @param mod_qrcodecheck_mod_form|null $mform Form.
 * @return bool
 */
function qrcodecheck_update_instance($data, $mform = null) {
    return manager::update($data);
}

/**
 * Deletes an activity instance.
 *
 * @param int $id Instance id.
 * @return bool
 */
function qrcodecheck_delete_instance($id) {
    return manager::delete($id);
}

/**
 * Adds cached activity data, including enabled custom completion rules.
 *
 * @param stdClass $coursemodule Course module record.
 * @return cached_cm_info|null
 */
function qrcodecheck_get_coursemodule_info($coursemodule) {
    global $DB;

    $instance = $DB->get_record(
        "qrcodecheck",
        ["id" => $coursemodule->instance],
        "id, name, completionscan"
    );
    if (!$instance) {
        return null;
    }

    $info = new cached_cm_info();
    $info->name = $instance->name;
    $info->customdata = [];
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $info->customdata["customcompletionrules"]["completionscan"] = (bool)$instance->completionscan;
    }

    return $info;
}
