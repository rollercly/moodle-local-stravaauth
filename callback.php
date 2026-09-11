<?php
// This file is part of Moodle - http://moodle.org/

// Callback OAuth2: Strava redirige aqui tras la autorizacion del usuario.
// Esta URL debe coincidir EXACTAMENTE con la registrada en strava.com/settings/api.
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
