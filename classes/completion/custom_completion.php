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

namespace mod_qrcodecheck\completion;

use core_completion\activity_custom_completion;

/**
 * Custom completion rules.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {
    /** @var string Completion rule name. */
    private const RULE_SCAN = "completionscan";

    /**
     * Returns completion state for a rule.
     *
     * @param string $rule Rule name.
     * @return int
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);
        if ($rule !== self::RULE_SCAN) {
            return COMPLETION_UNKNOWN;
        }

        $exists = $DB->record_exists("qrcodecheck_records", [
            "qrcodecheckid" => $this->cm->instance,
            "userid" => $this->userid,
        ]);
        return $exists ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Lists custom rules defined by this activity.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return [self::RULE_SCAN];
    }

    /**
     * Returns rule descriptions.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        return [self::RULE_SCAN => get_string("completionscan", "qrcodecheck")];
    }

    /**
     * Returns completion rule display order.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [self::RULE_SCAN];
    }
}
