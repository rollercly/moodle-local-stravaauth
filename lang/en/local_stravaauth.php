<?php
// This file is part of Moodle - http://moodle.org/

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
