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
 * OAuth2 callback: Strava redirects here after the user authorises.
 *
 * @package   local_stravaauth
 * @copyright 2026 Jose Lorenzo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This URL must match EXACTLY the one registered at strava.com/settings/api.
require_once(__DIR__ . '/../../config.php');

require_login();

$code  = optional_param('code', '', PARAM_RAW);
$state = optional_param('state', '', PARAM_RAW);
$error = optional_param('error', '', PARAM_RAW);

$returnurl = $SESSION->local_stravaauth_returnurl ?? (new moodle_url('/my/'))->out(false);
unset($SESSION->local_stravaauth_returnurl);

if ($error === 'access_denied') {
    redirect($returnurl, get_string('accessdenied', 'local_stravaauth'), null,
        \core\output\notification::NOTIFY_WARNING);
}

if (empty($code) || $state !== sesskey()) {
    throw new moodle_exception('errorinvalidcallback', 'local_stravaauth');
}

\local_stravaauth\api_client::exchange_code($USER->id, $code);

redirect($returnurl, get_string('connectsuccess', 'local_stravaauth'), null,
    \core\output\notification::NOTIFY_SUCCESS);
