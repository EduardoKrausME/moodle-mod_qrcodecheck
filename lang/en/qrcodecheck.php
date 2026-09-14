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
 * English strings.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['activesession'] = 'Active session';
$string['completionscan'] = 'Require a confirmed QR scan';
$string['completionscan_help'] = 'The activity is completed when the learner successfully registers at least one valid QR scan.';
$string['endsession'] = 'End session';
$string['eventattendanceregistered'] = 'QR attendance registered';
$string['invalidaction'] = 'Invalid QR Code Check action.';
$string['invalidbrowser'] = 'This QR continuation belongs to another browser. Scan the current QR again.';
$string['invalidpending'] = 'This pending QR scan is invalid or has already been used.';
$string['ipdifferent'] = 'Different IP';
$string['ipsame'] = 'Same IP';
$string['ipstatus'] = 'IP comparison';
$string['lastsession'] = 'Last session';
$string['modulename'] = 'QR Code Check';
$string['modulename_help'] = 'Registers attendance or participation using a short-lived rotating QR code projected by the teacher.';
$string['modulenameplural'] = 'QR Code Checks';
$string['never'] = 'Never';
$string['newsession'] = 'Start a new session';
$string['nextqr'] = 'QR changes in';
$string['nosession'] = 'There is no active QR session.';
$string['nosessionsyet'] = 'No QR sessions have been created yet.';
$string['notregisteredyet'] = 'You have not registered a QR scan for this activity yet.';
$string['openprojection'] = 'Open projection';
$string['pendingexpired'] = 'The login period for this QR scan has expired. Scan the current QR again.';
$string['pluginadministration'] = 'QR Code check administration';
$string['pluginname'] = 'QR Code Check';
$string['privacy:metadata:pending'] = 'Temporary QR clicks stored before login is completed.';
$string['privacy:metadata:pending:ipaddress'] = 'The IP address seen during the pre-login QR click.';
$string['privacy:metadata:pending:scannedat'] = 'The timestamp of the pre-login QR click.';
$string['privacy:metadata:pending:userid'] = 'The user linked to a pending scan after authentication.';
$string['privacy:metadata:records'] = 'Confirmed QR attendance records.';
$string['privacy:metadata:records:ipaddress'] = 'The IP address seen when the QR was scanned.';
$string['privacy:metadata:records:ipmatch'] = 'Whether the learner IP matched the teacher/projector IP.';
$string['privacy:metadata:records:projectorip'] = 'The teacher/projector IP used as the session reference.';
$string['privacy:metadata:records:scannedat'] = 'The timestamp when the QR was scanned.';
$string['privacy:metadata:records:userid'] = 'The user who registered the QR scan.';
$string['privacy:metadata:sessions'] = 'QR projection sessions created by teachers.';
$string['privacy:metadata:sessions:endedat'] = 'The timestamp when the projection session ended.';
$string['privacy:metadata:sessions:projectorip'] = 'The IP address of the browser displaying the QR projection.';
$string['privacy:metadata:sessions:startedat'] = 'The timestamp when the projection session started.';
$string['privacy:metadata:sessions:startedby'] = 'The user who started the projection session.';
$string['projectorip'] = 'Teacher/projector IP';
$string['qrcodealt'] = 'Temporary QR code for attendance';
$string['qrcodecheck:addinstance'] = 'Add a QR Code Check activity';
$string['qrcodecheck:manage'] = 'Manage QR Code Check sessions';
$string['qrcodecheck:view'] = 'View and register in QR Code Check';
$string['qrcodecheck:viewreport'] = 'View QR Code Check reports';
$string['qrexpired'] = 'This QR code is invalid or has expired. Scan the QR currently shown by the teacher.';
$string['qrlifetime'] = 'Each QR remains valid for  seconds.';
$string['registered'] = 'Registration confirmed';
$string['registeredok'] = 'Your QR scan was registered successfully.';
$string['report'] = 'Attendance report';
$string['reportsummary'] = 'Registered: . Same IP: . Different IP: .';
$string['scaninstruction'] = 'Scan this QR code with your phone to register your presence or participation.';
$string['scantime'] = 'Scan time';
$string['session'] = 'Session';
$string['sessionended'] = 'The QR session was ended.';
$string['sessionstarted'] = 'Session started';
$string['startsession'] = 'Start QR session';
$string['student'] = 'Student';
$string['studentip'] = 'Student IP';
$string['taskcleanuppending'] = 'Clean old QR pending scans';
$string['teachercontrols'] = 'Teacher controls';
$string['viewreport'] = 'View report';
