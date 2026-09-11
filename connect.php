<?php
// This file is part of Moodle - http://moodle.org/

// Punto de entrada: inicia el flujo OAuth2 redirigiendo al usuario a Strava.
require_once(__DIR__ . '/../../config.php');

require_login();

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

// Guardamos la URL de retorno en sesion para usarla desde callback.php.
$SESSION->local_stravaauth_returnurl = $returnurl ?: (new moodle_url('/my/'))->out(false);

$state = sesskey();
$url = \local_stravaauth\api_client::get_authorize_url($state);

redirect($url);
