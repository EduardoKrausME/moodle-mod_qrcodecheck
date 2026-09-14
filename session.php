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
 * Starts or ends projection sessions.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

$id = required_param("id", PARAM_INT);
$action = required_param("action", PARAM_ALPHA);
require_sesskey();

$cm = get_coursemodule_from_id("qrcodecheck", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$qrcodecheck = $DB->get_record("qrcodecheck", ["id" => $cm->instance], "*", MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/qrcodecheck:manage", $context);

if ($action === "start" || $action === "new") {
    $session = \mod_qrcodecheck\session_manager::start($qrcodecheck->id, $USER->id);
    redirect(new moodle_url("/mod/qrcodecheck/project.php", ["id" => $cm->id, "sid" => $session->id]));
}

if ($action === "end") {
    $sid = required_param("sid", PARAM_INT);
    \mod_qrcodecheck\session_manager::end($sid, $qrcodecheck->id);
    redirect(new moodle_url("/mod/qrcodecheck/view.php", ["id" => $cm->id]), get_string("sessionended", "qrcodecheck"));
}

throw new moodle_exception("invalidaction", "qrcodecheck");
