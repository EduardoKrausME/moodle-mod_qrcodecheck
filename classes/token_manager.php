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
 * Rotating QR token manager.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class token_manager {
    /** @var int QR changes every 10 seconds. */
    public const ROTATE_SECONDS = 10;

    /** @var int Each generated QR remains valid for 20 seconds. */
    public const EXPIRE_SECONDS = 20;

    /**
     * Returns the current time slot.
     *
     * @param int|null $time Unix timestamp.
     * @return int
     */
    public static function slot(?int $time = null): int {
        $time = $time ?? time();
        return (int) floor($time / self::ROTATE_SECONDS);
    }

    /**
     * Creates the token signature for a session and slot.
     *
     * @param \stdClass $session Session.
     * @param int $slot Slot.
     * @return string
     */
    public static function signature(\stdClass $session, int $slot): string {
        $payload = $session->id . ":" . $slot;
        return hash_hmac("sha256", $payload, $session->secret);
    }

    /**
     * Validates a QR token without requiring a logged in user.
     *
     * @param \stdClass $session Session.
     * @param int $slot Slot from QR.
     * @param string $token Token from QR.
     * @param int|null $now Current timestamp.
     * @return bool
     */
    public static function validate(\stdClass $session, int $slot, string $token, ?int $now = null): bool {
        $now = $now ?? time();
        if (!empty($session->endedat)) {
            return false;
        }

        $slotstart = $slot * self::ROTATE_SECONDS;
        if ($now < $slotstart || $now >= ($slotstart + self::EXPIRE_SECONDS)) {
            return false;
        }

        return hash_equals(self::signature($session, $slot), $token);
    }

    /**
     * Builds the current scan URL.
     *
     * @param \stdClass $session Session.
     * @return \moodle_url
     */
    public static function scan_url(\stdClass $session): \moodle_url {
        $slot = self::slot();
        return new \moodle_url("/mod/qrcodecheck/scan.php", [
            "s" => $session->id,
            "slot" => $slot,
            "t" => self::signature($session, $slot),
        ]);
    }
}
