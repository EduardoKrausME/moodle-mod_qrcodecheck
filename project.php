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
 * Projection screen with rotating QR code.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

$id = required_param("id", PARAM_INT);
$sid = optional_param("sid", 0, PARAM_INT);
$cm = get_coursemodule_from_id("qrcodecheck", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$qrcodecheck = $DB->get_record("qrcodecheck", ["id" => $cm->instance], "*", MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/qrcodecheck:manage", $context);

$session = $sid
    ? $DB->get_record("qrcodecheck_sessions", ["id" => $sid, "qrcodecheckid" => $qrcodecheck->id], "*", MUST_EXIST)
    : \mod_qrcodecheck\session_manager::get_active($qrcodecheck->id);

if (!$session || !empty($session->endedat)) {
    redirect(new moodle_url("/mod/qrcodecheck/view.php", ["id" => $cm->id]), get_string("nosession", "qrcodecheck"));
}

if ($session->projectorip === "") {
    $session->projectorip = \mod_qrcodecheck\ip::current();
    $DB->set_field("qrcodecheck_sessions", "projectorip", $session->projectorip, ["id" => $session->id]);
}

$PAGE->set_url("/mod/qrcodecheck/project.php", ["id" => $cm->id, "sid" => $session->id]);
$PAGE->set_title(format_string($qrcodecheck->name));
$PAGE->set_heading(format_string($qrcodecheck->name));
$PAGE->requires->js_call_amd("mod_qrcodecheck/project", "init", [
    $cm->id,
    $session->id,
    \mod_qrcodecheck\token_manager::ROTATE_SECONDS,
    \mod_qrcodecheck\token_manager::EXPIRE_SECONDS,
]);

$data = [
    "name" => format_string($qrcodecheck->name),
    "qrurl" => (new moodle_url("/mod/qrcodecheck/qr.php", ["id" => $cm->id, "sid" => $session->id]))->out(false),
    "projectorip" => s($session->projectorip),
    "startedat" => userdate($session->startedat),
    "sessionaction" => (new moodle_url("/mod/qrcodecheck/session.php"))->out(false),
    "reporturl" => (new moodle_url("/mod/qrcodecheck/report.php", ["id" => $cm->id, "sid" => $session->id]))->out(false),
    "cmid" => $cm->id,
    "sid" => $session->id,
    "sesskey" => sesskey(),
    "rotate" => \mod_qrcodecheck\token_manager::ROTATE_SECONDS,
    "expiry" => \mod_qrcodecheck\token_manager::EXPIRE_SECONDS,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template("mod_qrcodecheck/project", $data);
echo $OUTPUT->footer();
