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
 * Privacy API provider for local_stravaauth.
 *
 * @package   local_stravaauth
 * @copyright 2026 Jose Lorenzo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stravaauth\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider: Strava tokens are personal data of the user.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\user_preference_provider,
    \core_privacy\local\request\plugin\provider {

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_stravaauth_token', [
            'userid'       => 'privacy:metadata:local_stravaauth_token:userid',
            'athleteid'    => 'privacy:metadata:local_stravaauth_token:athleteid',
            'accesstoken'  => 'privacy:metadata:local_stravaauth_token:accesstoken',
            'refreshtoken' => 'privacy:metadata:local_stravaauth_token:refreshtoken',
        ], 'privacy:metadata:local_stravaauth_token');

        $collection->add_external_location_link('strava.com', [
            'accesstoken' => 'privacy:metadata:local_stravaauth_token:accesstoken',
        ], 'privacy:metadata:local_stravaauth');

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $contextlist->add_user_context($userid);
        return $contextlist;
    }

    public static function get_users_in_context(\core_privacy\local\request\userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_USER) {
            return;
        }
        $userlist->add_from_sql('userid',
            'SELECT userid FROM {local_stravaauth_token} WHERE userid = :userid',
            ['userid' => $context->instanceid]);
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $user = $contextlist->get_user();
        $record = $DB->get_record('local_stravaauth_token', ['userid' => $user->id]);
        if ($record) {
            writer::with_context(\context_user::instance($user->id))->export_data(
                [get_string('pluginname', 'local_stravaauth')],
                (object) [
                    'athleteid'    => $record->athleteid,
                    'timecreated'  => \core_privacy\local\request\transform::datetime($record->timecreated),
                    'timemodified' => \core_privacy\local\request\transform::datetime($record->timemodified),
                ]
            );
        }
    }

    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;
        if ($context->contextlevel !== CONTEXT_USER) {
            return;
        }
        $DB->delete_records('local_stravaauth_token', ['userid' => $context->instanceid]);
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $DB->delete_records('local_stravaauth_token', ['userid' => $contextlist->get_user()->id]);
    }

    public static function delete_data_for_users(\core_privacy\local\request\approved_userlist $userlist): void {
        global $DB;
        foreach ($userlist->get_userids() as $userid) {
            $DB->delete_records('local_stravaauth_token', ['userid' => $userid]);
        }
    }

    public static function export_user_preferences(int $userid): void {
        // This plugin has no user preferences of its own.
    }
}
