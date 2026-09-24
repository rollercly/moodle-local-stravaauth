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
 * Admin settings for local_stravaauth.
 *
 * @package   local_stravaauth
 * @copyright 2026 Jose Lorenzo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_stravaauth', get_string('pluginname', 'local_stravaauth'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext(
        'local_stravaauth/clientid',
        get_string('clientid', 'local_stravaauth'),
        get_string('clientid_desc', 'local_stravaauth'),
        '',
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_stravaauth/clientsecret',
        get_string('clientsecret', 'local_stravaauth'),
        get_string('clientsecret_desc', 'local_stravaauth'),
        ''
    ));

    // Informational callback URL: register it as is in the Strava dashboard (strava.com/settings/api).
    $callbackurl = (new moodle_url('/local/stravaauth/callback.php'))->out(false);
    $settings->add(new admin_setting_heading(
        'local_stravaauth/callbackinfo',
        '',
        get_string('callbackurl_desc', 'local_stravaauth', $callbackurl)
    ));
}
