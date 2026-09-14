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
 * Public QR scan entry point.
 *
 * The first valid QR click is persisted before require_login(). This allows a user
 * to authenticate after the QR has rotated without losing the original valid click.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

global $SESSION;

$pendingid = optional_param("p", 0, PARAM_INT);
$key = optional_param("k", "", PARAM_ALPHANUM);

if ($pendingid && $key !== "") {
    $pending = \mod_qrcodecheck\scan_manager::get_valid_pending($pendingid, $key);
    $browserkey = $SESSION->qrcodecheck_pending[$pendingid] ?? "";
    if ($browserkey === "" || !hash_equals($browserkey, hash("sha256", $key))) {
        throw new moodle_exception("invalidbrowser", "qrcodecheck");
    }
    $qrcodecheck = $DB->get_record("qrcodecheck", ["id" => $pending->qrcodecheckid], "*", MUST_EXIST);
    $cm = get_coursemodule_from_instance("qrcodecheck", $qrcodecheck->id, $qrcodecheck->course, false, MUST_EXIST);
    $course = get_course($qrcodecheck->course);

    require_login($course, true, $cm);
    $context = context_module::instance($cm->id);
    require_capability("mod/qrcodecheck:view", $context);

    $record = \mod_qrcodecheck\scan_manager::finalize($pending, $USER->id);
    unset($SESSION->qrcodecheck_pending[$pendingid]);

    $event = \mod_qrcodecheck\event\attendance_registered::create([
        "objectid" => $record->id,
        "context" => $context,
        "userid" => $USER->id,
        "other" => ["sessionid" => $record->sessionid, "ipmatch" => $record->ipmatch],
    ]);
    $event->trigger();

    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && !empty($qrcodecheck->completionscan)) {
        $completion->update_state($cm, COMPLETION_COMPLETE, $USER->id);
    }

    redirect(
        new moodle_url("/mod/qrcodecheck/view.php", ["id" => $cm->id]),
        get_string("registeredok", "qrcodecheck"),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$sid = required_param("s", PARAM_INT);
$slot = required_param("slot", PARAM_INT);
$token = required_param("t", PARAM_ALPHANUM);

$session = $DB->get_record("qrcodecheck_sessions", ["id" => $sid], "*", MUST_EXIST);
if (!\mod_qrcodecheck\token_manager::validate($session, $slot, $token)) {
    throw new moodle_exception("qrexpired", "qrcodecheck");
}

$key = bin2hex(random_bytes(24));
$pending = \mod_qrcodecheck\scan_manager::create_pending($session, $key);
if (!isset($SESSION->qrcodecheck_pending) || !is_array($SESSION->qrcodecheck_pending)) {
    $SESSION->qrcodecheck_pending = [];
}
$SESSION->qrcodecheck_pending[$pending->id] = hash("sha256", $key);

redirect(new moodle_url("/mod/qrcodecheck/scan.php", [
    "p" => $pending->id,
    "k" => $key,
]));
