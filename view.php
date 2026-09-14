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
 * Activity view.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("qrcodecheck", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$qrcodecheck = $DB->get_record("qrcodecheck", ["id" => $cm->instance], "*", MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/qrcodecheck:view", $context);

$PAGE->set_url("/mod/qrcodecheck/view.php", ["id" => $cm->id]);
$PAGE->set_title(format_string($qrcodecheck->name));
$PAGE->set_heading(format_string($course->fullname));

$active = \mod_qrcodecheck\session_manager::get_active($qrcodecheck->id);
$record = \mod_qrcodecheck\scan_manager::latest_for_user($qrcodecheck->id, $USER->id);
$canmanage = has_capability("mod/qrcodecheck:manage", $context);
$canreport = has_capability("mod/qrcodecheck:viewreport", $context);

$data = [
    "name" => format_string($qrcodecheck->name),
    "intro" => format_module_intro("qrcodecheck", $qrcodecheck, $cm->id),
    "canmanage" => $canmanage,
    "canreport" => $canreport,
    "hasactive" => (bool) $active,
    "projecturl" => $active ? (new moodle_url("/mod/qrcodecheck/project.php", ["id" => $cm->id]))->out(false) : "",
    "reporturl" => (new moodle_url("/mod/qrcodecheck/report.php", ["id" => $cm->id]))->out(false),
    "sessionaction" => (new moodle_url("/mod/qrcodecheck/session.php"))->out(false),
    "cmid" => $cm->id,
    "sesskey" => sesskey(),
    "registered" => (bool) $record,
    "registeredtime" => $record ? userdate($record->scannedat) : "",
];

if ($active) {
    $data["sessionstarted"] = userdate($active->startedat);
}

if ($record) {
    $data["ipstatus"] = $record->ipmatch
        ? get_string("ipsame", "qrcodecheck")
        : get_string("ipdifferent", "qrcodecheck");
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template("mod_qrcodecheck/view", $data);
echo $OUTPUT->footer();
