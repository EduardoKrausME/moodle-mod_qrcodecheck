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

use stdClass;

/**
 * Projection session manager.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session_manager {
    /**
     * Starts a new projection session and closes any previous active session.
     *
     * @param int $qrcodecheckid Activity id.
     * @param int $userid Teacher id.
     * @return stdClass
     */
    public static function start(int $qrcodecheckid, int $userid): stdClass {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        $now = time();
        $DB->set_field_select(
            "qrcodecheck_sessions",
            "endedat",
            $now,
            "qrcodecheckid = :qid AND endedat = 0",
            ["qid" => $qrcodecheckid]
        );

        $record = (object)[
            "qrcodecheckid" => $qrcodecheckid,
            "startedby" => $userid,
            "startedat" => $now,
            "endedat" => 0,
            "projectorip" => "",
            "secret" => bin2hex(random_bytes(32)),
            "timecreated" => $now,
        ];
        $record->id = $DB->insert_record("qrcodecheck_sessions", $record);
        $transaction->allow_commit();
        return $record;
    }

    /**
     * Returns the active session.
     *
     * @param int $qrcodecheckid Activity id.
     * @return stdClass|null
     */
    public static function get_active(int $qrcodecheckid): ?stdClass {
        global $DB;

        $record = $DB->get_record(
            "qrcodecheck_sessions",
            ["qrcodecheckid" => $qrcodecheckid, "endedat" => 0],
            "*",
            IGNORE_MULTIPLE
        );
        return $record ?: null;
    }

    /**
     * Ends an active session.
     *
     * @param int $sessionid Session id.
     * @param int $qrcodecheckid Activity id.
     * @return void
     */
    public static function end(int $sessionid, int $qrcodecheckid): void {
        global $DB;

        $DB->set_field("qrcodecheck_sessions", "endedat", time(), [
            "id" => $sessionid,
            "qrcodecheckid" => $qrcodecheckid,
            "endedat" => 0,
        ]);
    }
}
