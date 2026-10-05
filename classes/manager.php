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
 * Activity instance manager.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /**
     * Creates an instance.
     *
     * @param stdClass $data Data.
     * @return int
     */
    public static function create(stdClass $data): int {
        global $DB;

        $now = time();
        $data->timecreated = $now;
        $data->timemodified = $now;

        if (property_exists($data, "completionscan")) {
            $data->completionscan = empty($data->completionscan) ? 0 : 1;
        } else if (($data->completion ?? COMPLETION_TRACKING_NONE) == COMPLETION_TRACKING_AUTOMATIC) {
            // Programmatic creation does not pass through mod_form, so mirror the form default.
            $data->completionscan = 1;
        } else {
            $data->completionscan = 0;
        }

        return $DB->insert_record("qrcodecheck", $data);
    }

    /**
     * Updates an instance.
     *
     * @param stdClass $data Data.
     * @return bool
     */
    public static function update(stdClass $data): bool {
        global $DB;

        $data->id = $data->instance;
        $data->timemodified = time();

        if (property_exists($data, "completionscan")) {
            $data->completionscan = empty($data->completionscan) ? 0 : 1;
        }

        return $DB->update_record("qrcodecheck", $data);
    }

    /**
     * Deletes an instance and its data.
     *
     * @param int $id Instance id.
     * @return bool
     */
    public static function delete(int $id): bool {
        global $DB;

        if (!$DB->record_exists("qrcodecheck", ["id" => $id])) {
            return false;
        }

        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records("qrcodecheck_pending", ["qrcodecheckid" => $id]);
        $DB->delete_records("qrcodecheck_records", ["qrcodecheckid" => $id]);
        $DB->delete_records("qrcodecheck_sessions", ["qrcodecheckid" => $id]);
        $DB->delete_records("qrcodecheck", ["id" => $id]);
        $transaction->allow_commit();
        return true;
    }
}
