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
 * Attendance report.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("{$CFG->libdir}/tablelib.php");

$id = required_param("id", PARAM_INT);
$sid = optional_param("sid", 0, PARAM_INT);
$cm = get_coursemodule_from_id("qrcodecheck", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$qrcodecheck = $DB->get_record("qrcodecheck", ["id" => $cm->instance], "*", MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/qrcodecheck:viewreport", $context);

if ($sid) {
    $session = $DB->get_record("qrcodecheck_sessions", ["id" => $sid, "qrcodecheckid" => $qrcodecheck->id], "*", MUST_EXIST);
} else {
    $sessions = $DB->get_records("qrcodecheck_sessions", ["qrcodecheckid" => $qrcodecheck->id], "startedat DESC", "*", 0, 1);
    $session = $sessions ? reset($sessions) : null;
    $sid = $session ? (int) $session->id : 0;
}

$PAGE->set_url("/mod/qrcodecheck/report.php", ["id" => $cm->id, "sid" => $sid]);
$PAGE->set_title(get_string("report", "qrcodecheck"));
$PAGE->set_heading(format_string($qrcodecheck->name));

$sessions = $DB->get_records("qrcodecheck_sessions", ["qrcodecheckid" => $qrcodecheck->id], "startedat DESC");
$sessionoptions = [];
foreach ($sessions as $item) {
    $sessionoptions[$item->id] = userdate($item->startedat) . " - " . $item->projectorip;
}

$records = [];
if ($session) {
    $sql = "SELECT r.*, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                   u.middlename, u.alternatename, u.email
              FROM {qrcodecheck_records} r
              JOIN {user} u ON u.id = r.userid
             WHERE r.sessionid = :sid
          ORDER BY r.scannedat ASC";
    $records = $DB->get_records_sql($sql, ["sid" => $session->id]);
}

$total = count($records);
$different = 0;
foreach ($records as $item) {
    if (!$item->ipmatch) {
        $different++;
    }
}

$table = new flexible_table("mod-qrcodecheck-report-{$sid}");
$table->define_columns(["user", "time", "studentip", "projectorip", "ipstatus"]);
$table->define_headers([
    get_string("student", "qrcodecheck"),
    get_string("scantime", "qrcodecheck"),
    get_string("studentip", "qrcodecheck"),
    get_string("projectorip", "qrcodecheck"),
    get_string("ipstatus", "qrcodecheck"),
]);
$table->define_baseurl($PAGE->url);
$table->set_attribute("class", "generaltable generalbox");
$table->setup();

foreach ($records as $item) {
    $status = $item->ipmatch
        ? get_string("ipsame", "qrcodecheck")
        : html_writer::tag("strong", get_string("ipdifferent", "qrcodecheck"));
    $table->add_data([
        fullname($item),
        userdate($item->scannedat),
        s($item->ipaddress),
        s($item->projectorip),
        $status,
    ]);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("report", "qrcodecheck"));

if ($sessionoptions) {
    echo html_writer::start_tag("form",
        ["method" => "get", "action" => (new moodle_url("/mod/qrcodecheck/report.php"))->out(false)]);
    echo html_writer::empty_tag("input",
        ["type" => "hidden", "name" => "id", "value" => $cm->id]);
    echo html_writer::label(get_string("session", "qrcodecheck"), "id_sid", false,
        ["class" => "me-2"]);
    echo html_writer::select($sessionoptions, "sid", $sid, false,
        ["id" => "id_sid", "onchange" => "this.form.submit()"]);
    echo html_writer::end_tag("form");
}

if ($session) {
    echo html_writer::div(get_string("reportsummary", "qrcodecheck", (object) [
        "total" => $total,
        "same" => $total - $different,
        "different" => $different,
    ]), "alert alert-info mt-3");
    $table->finish_output();
} else {
    echo $OUTPUT->notification(get_string("nosessionsyet", "qrcodecheck"), \core\output\notification::NOTIFY_INFO);
}

echo $OUTPUT->footer();
