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

namespace mod_qrcodecheck;

/**
 * Scan and attendance manager.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class scan_manager {
    /** @var int Time allowed to finish login after a valid QR click. */
    public const LOGIN_GRACE_SECONDS = 900;

    /**
     * Records the QR click before Moodle login is required.
     *
     * @param \stdClass $session Session.
     * @param string $key Plain one-time pending key.
     * @return \stdClass
     */
    public static function create_pending(\stdClass $session, string $key): \stdClass {
        global $DB;

        $record = (object) [
            "qrcodecheckid" => $session->qrcodecheckid,
            "sessionid" => $session->id,
            "keyhash" => hash("sha256", $key),
            "scannedat" => time(),
            "ipaddress" => ip::current(),
            "userid" => 0,
            "status" => "pending",
            "finalizedat" => 0,
            "timecreated" => time(),
        ];
        $record->id = $DB->insert_record("qrcodecheck_pending", $record);
        return $record;
    }

    /**
     * Loads and validates a pending scan secret.
     *
     * @param int $pendingid Pending id.
     * @param string $key Plain key.
     * @return \stdClass
     */
    public static function get_valid_pending(int $pendingid, string $key): \stdClass {
        global $DB;

        $pending = $DB->get_record("qrcodecheck_pending", ["id" => $pendingid], "*", MUST_EXIST);
        if ($pending->status !== "pending" || !hash_equals($pending->keyhash, hash("sha256", $key))) {
            throw new \moodle_exception("invalidpending", "qrcodecheck");
        }
        if ((time() - (int) $pending->scannedat) > self::LOGIN_GRACE_SECONDS) {
            throw new \moodle_exception("pendingexpired", "qrcodecheck");
        }
        return $pending;
    }

    /**
     * Finalizes a pending scan for the authenticated user.
     *
     * @param \stdClass $pending Pending scan.
     * @param int $userid User id.
     * @return \stdClass Attendance record.
     */
    public static function finalize(\stdClass $pending, int $userid): \stdClass {
        global $DB;

        $session = $DB->get_record("qrcodecheck_sessions", ["id" => $pending->sessionid], "*", MUST_EXIST);
        $existing = $DB->get_record("qrcodecheck_records", [
            "sessionid" => $pending->sessionid,
            "userid" => $userid,
        ]);

        $transaction = $DB->start_delegated_transaction();
        if (!$existing) {
            $record = (object) [
                "qrcodecheckid" => $pending->qrcodecheckid,
                "sessionid" => $pending->sessionid,
                "userid" => $userid,
                "scannedat" => $pending->scannedat,
                "ipaddress" => $pending->ipaddress,
                "projectorip" => $session->projectorip,
                "ipmatch" => ip::matches($pending->ipaddress, $session->projectorip) ? 1 : 0,
                "timecreated" => time(),
                "timemodified" => time(),
            ];
            $record->id = $DB->insert_record("qrcodecheck_records", $record);
        } else {
            $record = $existing;
        }

        $pending->userid = $userid;
        $pending->status = "completed";
        $pending->finalizedat = time();
        $DB->update_record("qrcodecheck_pending", $pending);
        $transaction->allow_commit();

        return $record;
    }

    /**
     * Returns the latest record for a user in an activity.
     *
     * @param int $qrcodecheckid Activity id.
     * @param int $userid User id.
     * @return \stdClass|null
     */
    public static function latest_for_user(int $qrcodecheckid, int $userid): ?\stdClass {
        global $DB;

        $records = $DB->get_records(
            "qrcodecheck_records",
            ["qrcodecheckid" => $qrcodecheckid, "userid" => $userid],
            "scannedat DESC",
            "*",
            0,
            1
        );
        return $records ? reset($records) : null;
    }
}
