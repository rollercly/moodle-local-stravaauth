<?php
// This file is part of Moodle - http://moodle.org/

$string['pluginname'] = 'Autenticación con Strava';
$string['clientid'] = 'Client ID de Strava';
$string['clientid_desc'] = 'Client ID de la aplicación, obtenido en strava.com/settings/api.';
$string['clientsecret'] = 'Client Secret de Strava';
$string['clientsecret_desc'] = 'Client Secret de la aplicación, obtenido en strava.com/settings/api.';
$string['callbackurl_desc'] = 'Registra esta URL exacta como "Authorization Callback Domain/URI" en tu aplicación de Strava: {$a}';
$string['accessdenied'] = 'Has rechazado el acceso a tu cuenta de Strava.';
$string['connectsuccess'] = 'Tu cuenta de Strava se ha vinculado correctamente.';
$string['errorinvalidcallback'] = 'Callback de autorización de Strava inválido o caducado.';
$string['errornotconnected'] = 'Este usuario no ha vinculado una cuenta de Strava.';
$string['errortokenexchange'] = 'No se ha podido canjear el código de autorización por un token de Strava.';
$string['errortokenrefresh'] = 'No se ha podido refrescar el token de acceso de Strava.';
$string['errorapicall'] = 'Ha fallado la llamada a la API de Strava.';
$string['connecttostrava'] = 'Conectar con Strava';
$string['connectedas'] = 'Conectado a Strava como atleta #{$a}';
$string['privacy:metadata:local_stravaauth_token'] = 'Almacena los tokens OAuth2 que vinculan a un usuario de Moodle con su cuenta de Strava.';
$string['privacy:metadata:local_stravaauth_token:userid'] = 'El ID del usuario de Moodle.';
$string['privacy:metadata:local_stravaauth_token:athleteid'] = 'El ID del atleta de Strava vinculado.';
$string['privacy:metadata:local_stravaauth_token:accesstoken'] = 'El token de acceso OAuth2.';
$string['privacy:metadata:local_stravaauth_token:refreshtoken'] = 'El token de refresco OAuth2.';
$string['privacy:metadata:local_stravaauth'] = 'Para mostrar y calificar actividades, se intercambian datos del usuario con la API de Strava.';
