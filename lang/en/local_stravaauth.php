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
 * English language strings for local_stravaauth.
 *
 * @package   local_stravaauth
 * @copyright 2026 Jose Lorenzo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Strava authentication';
$string['clientid'] = 'Strava Client ID';
$string['clientid_desc'] = 'Application Client ID, from strava.com/settings/api.';
$string['clientsecret'] = 'Strava Client Secret';
$string['clientsecret_desc'] = 'Application Client Secret, from strava.com/settings/api.';
$string['callbackurl_desc'] = 'Register this exact Authorization Callback Domain / URI in your Strava API application: {$a}';
$string['accessdenied'] = 'You declined access to your Strava account.';
$string['connectsuccess'] = 'Your Strava account has been linked successfully.';
$string['errorinvalidcallback'] = 'Invalid or expired Strava authorisation callback.';
$string['errornotconnected'] = 'This user has not linked a Strava account.';
$string['errortokenexchange'] = 'Could not exchange the authorisation code for a Strava token.';
$string['errortokenrefresh'] = 'Could not refresh the Strava access token.';
$string['errorapicall'] = 'Strava API call failed.';
$string['connecttostrava'] = 'Connect with Strava';
$string['connectedas'] = 'Connected to Strava as athlete #{$a}';
$string['privacy:metadata:local_stravaauth_token'] = 'Stores the OAuth2 tokens that link a Moodle user to their Strava account.';
$string['privacy:metadata:local_stravaauth_token:userid'] = 'The Moodle user ID.';
$string['privacy:metadata:local_stravaauth_token:athleteid'] = 'The linked Strava athlete ID.';
$string['privacy:metadata:local_stravaauth_token:accesstoken'] = 'The OAuth2 access token.';
$string['privacy:metadata:local_stravaauth_token:refreshtoken'] = 'The OAuth2 refresh token.';
$string['privacy:metadata:local_stravaauth'] = 'To display and grade activities, user data is exchanged with the Strava API.';
