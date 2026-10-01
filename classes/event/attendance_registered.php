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

namespace mod_qrcodecheck\event;

use core\event\base;
use moodle_url;

/**
 * Attendance registered event.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attendance_registered extends base {
    /**
     * Initializes the event.
     *
     * @return void
     */
    protected function init() {
        $this->data["crud"] = "c";
        $this->data["edulevel"] = self::LEVEL_PARTICIPATING;
        $this->data["objecttable"] = "qrcodecheck_records";
    }

    /**
     * Event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string("eventattendanceregistered", "qrcodecheck");
    }

    /**
     * Description.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '{$this->userid}' registered a QR Code Check attendance record.";
    }

    /**
     * URL for the event.
     *
     * @return moodle_url
     */
    public function get_url() {
        return new moodle_url("/mod/qrcodecheck/view.php", ["id" => $this->contextinstanceid]);
    }
}
