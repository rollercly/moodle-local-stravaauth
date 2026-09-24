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
 * Entry point that starts the OAuth2 flow by redirecting the user to Strava.
 *
 * @package   local_stravaauth
 * @copyright 2026 Jose Lorenzo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

// Store the return URL in the session so callback.php can use it.
$SESSION->local_stravaauth_returnurl = $returnurl ?: (new moodle_url('/my/'))->out(false);

$state = sesskey();
$url = \local_stravaauth\api_client::get_authorize_url($state);

redirect($url);
