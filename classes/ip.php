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
 * IP address helper.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ip {
    /**
     * Returns the client IP as seen by Moodle.
     *
     * @return string
     */
    public static function current(): string {
        $address = getremoteaddr();
        return $address === null ? "" : (string) $address;
    }

    /**
     * Compares two IP strings.
     *
     * @param string $studentip Student IP.
     * @param string $projectorip Projector IP.
     * @return bool
     */
    public static function matches(string $studentip, string $projectorip): bool {
        return $studentip !== "" && $projectorip !== "" && hash_equals($projectorip, $studentip);
    }
}
