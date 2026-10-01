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
 * Serves the current QR image to the teacher projection page.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_qrcodecheck\token_manager;

require_once("../../config.php");

$id = required_param("id", PARAM_INT);
$sid = required_param("sid", PARAM_INT);
$cm = get_coursemodule_from_id("qrcodecheck", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$qrcodecheck = $DB->get_record("qrcodecheck", ["id" => $cm->instance], "*", MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/qrcodecheck:manage", $context);

$session = $DB->get_record("qrcodecheck_sessions", [
    "id" => $sid,
    "qrcodecheckid" => $qrcodecheck->id,
    "endedat" => 0,
], "*", MUST_EXIST);

$url = token_manager::scan_url($session)->out(false);
$qrcode = new core_qrcode($url);
$png = $qrcode->getBarcodePngData(10, 10);

header("Content-Type: image/png");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
echo $png;
exit;
