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

namespace mod_qrcodecheck\privacy;

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider.
 *
 * @package mod_qrcodecheck
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    core_userlist_provider {

    /**
     * Describes stored personal data.
     *
     * @param collection $collection Collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table("qrcodecheck_sessions", [
            "startedby" => "privacy:metadata:sessions:startedby",
            "startedat" => "privacy:metadata:sessions:startedat",
            "endedat" => "privacy:metadata:sessions:endedat",
            "projectorip" => "privacy:metadata:sessions:projectorip",
        ], "privacy:metadata:sessions");
        $collection->add_database_table("qrcodecheck_records", [
            "userid" => "privacy:metadata:records:userid",
            "scannedat" => "privacy:metadata:records:scannedat",
            "ipaddress" => "privacy:metadata:records:ipaddress",
            "projectorip" => "privacy:metadata:records:projectorip",
            "ipmatch" => "privacy:metadata:records:ipmatch",
        ], "privacy:metadata:records");
        $collection->add_database_table("qrcodecheck_pending", [
            "userid" => "privacy:metadata:pending:userid",
            "scannedat" => "privacy:metadata:pending:scannedat",
            "ipaddress" => "privacy:metadata:pending:ipaddress",
        ], "privacy:metadata:pending");
        return $collection;
    }

    /**
     * Returns contexts containing data for a user.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel1
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname1
                  JOIN {qrcodecheck} q ON q.id = cm.instance
                  JOIN {qrcodecheck_records} r ON r.qrcodecheckid = q.id
                 WHERE r.userid = :userid1
                 UNION
                SELECT ctx2.id
                  FROM {context} ctx2
                  JOIN {course_modules} cm2 ON cm2.id = ctx2.instanceid AND ctx2.contextlevel = :contextlevel2
                  JOIN {modules} m2 ON m2.id = cm2.module AND m2.name = :modname2
                  JOIN {qrcodecheck} q2 ON q2.id = cm2.instance
                  JOIN {qrcodecheck_sessions} s ON s.qrcodecheckid = q2.id
                 WHERE s.startedby = :userid2";
        $contextlist->add_from_sql($sql, [
            "contextlevel1" => CONTEXT_MODULE,
            "modname1" => "qrcodecheck",
            "userid1" => $userid,
            "contextlevel2" => CONTEXT_MODULE,
            "modname2" => "qrcodecheck",
            "userid2" => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Exports data for approved contexts.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!($context instanceof context_module)) {
                continue;
            }
            $cm = get_coursemodule_from_id("qrcodecheck", $context->instanceid);
            if (!$cm) {
                continue;
            }
            $records = $DB->get_records("qrcodecheck_records", [
                "qrcodecheckid" => $cm->instance,
                "userid" => $userid,
            ], "scannedat ASC");
            $export = [];
            foreach ($records as $record) {
                $export[] = (object)[
                    "scannedat" => transform::datetime($record->scannedat),
                    "ipaddress" => $record->ipaddress,
                    "projectorip" => $record->projectorip,
                    "ipmatch" => (bool)$record->ipmatch,
                ];
            }
            $sessions = $DB->get_records("qrcodecheck_sessions", [
                "qrcodecheckid" => $cm->instance,
                "startedby" => $userid,
            ], "startedat ASC");
            $sessionexport = [];
            foreach ($sessions as $session) {
                $sessionexport[] = (object)[
                    "startedat" => transform::datetime($session->startedat),
                    "endedat" => $session->endedat ? transform::datetime($session->endedat) : null,
                    "projectorip" => $session->projectorip,
                ];
            }
            writer::with_context($context)->export_data([], (object)[
                "records" => $export,
                "sessions" => $sessionexport,
            ]);
        }
    }

    /**
     * Deletes all user data from a module context.
     *
     * @param context $context Context.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if (!($context instanceof context_module)) {
            return;
        }
        $cm = get_coursemodule_from_id("qrcodecheck", $context->instanceid);
        if (!$cm) {
            return;
        }
        $DB->delete_records("qrcodecheck_pending", ["qrcodecheckid" => $cm->instance]);
        $DB->delete_records("qrcodecheck_records", ["qrcodecheckid" => $cm->instance]);
        $DB->delete_records("qrcodecheck_sessions", ["qrcodecheckid" => $cm->instance]);
    }

    /**
     * Deletes data for one user.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!($context instanceof context_module)) {
                continue;
            }
            $cm = get_coursemodule_from_id("qrcodecheck", $context->instanceid);
            if (!$cm) {
                continue;
            }
            $sessions = $DB->get_records("qrcodecheck_sessions", [
                "qrcodecheckid" => $cm->instance,
                "startedby" => $userid,
            ], "", "id");
            if ($sessions) {
                [$insql, $params] = $DB->get_in_or_equal(array_keys($sessions), SQL_PARAMS_NAMED, "sid");
                $DB->set_field_select("qrcodecheck_records", "projectorip", "", "sessionid {$insql}", $params);
            }
            $DB->delete_records("qrcodecheck_records", ["qrcodecheckid" => $cm->instance, "userid" => $userid]);
            $DB->delete_records("qrcodecheck_pending", ["qrcodecheckid" => $cm->instance, "userid" => $userid]);
            $DB->set_field_select(
                "qrcodecheck_sessions",
                "projectorip",
                "",
                "qrcodecheckid = :qid AND startedby = :userid",
                ["qid" => $cm->instance, "userid" => $userid]
            );
            $DB->set_field_select(
                "qrcodecheck_sessions",
                "startedby",
                0,
                "qrcodecheckid = :qid AND startedby = :userid",
                ["qid" => $cm->instance, "userid" => $userid]
            );
        }
    }

    /**
     * Adds users with data in a context.
     *
     * @param userlist $userlist User list.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!($context instanceof context_module)) {
            return;
        }
        $cm = get_coursemodule_from_id("qrcodecheck", $context->instanceid);
        if (!$cm) {
            return;
        }
        $sql = "SELECT userid FROM {qrcodecheck_records} WHERE qrcodecheckid = :qid";
        $userlist->add_from_sql("userid", $sql, ["qid" => $cm->instance]);
        $sessionusers = "SELECT startedby AS userid
                           FROM {qrcodecheck_sessions}
                          WHERE qrcodecheckid = :qid AND startedby > 0";
        $userlist->add_from_sql("userid", $sessionusers, ["qid" => $cm->instance]);
    }

    /**
     * Deletes data for an approved list of users.
     *
     * @param approved_userlist $userlist Approved user list.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!($context instanceof context_module)) {
            return;
        }
        $cm = get_coursemodule_from_id("qrcodecheck", $context->instanceid);
        if (!$cm) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED, "uid");
        $params["qid"] = $cm->instance;
        $sessions = $DB->get_records_select(
            "qrcodecheck_sessions",
            "qrcodecheckid = :qid AND startedby {$insql}",
            $params,
            "",
            "id"
        );
        if ($sessions) {
            [$sessioninsql, $sessionparams] = $DB->get_in_or_equal(array_keys($sessions), SQL_PARAMS_NAMED, "sid");
            $DB->set_field_select("qrcodecheck_records", "projectorip", "", "sessionid {$sessioninsql}", $sessionparams);
        }
        $DB->delete_records_select("qrcodecheck_records", "qrcodecheckid = :qid AND userid {$insql}", $params);
        $DB->delete_records_select("qrcodecheck_pending", "qrcodecheckid = :qid AND userid {$insql}", $params);
        $DB->set_field_select("qrcodecheck_sessions", "projectorip", "", "qrcodecheckid = :qid AND startedby {$insql}", $params);
        $DB->set_field_select("qrcodecheck_sessions", "startedby", 0, "qrcodecheckid = :qid AND startedby {$insql}", $params);
    }
}
