<?php
// This file is part of Moodle - http://moodle.org/
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

    // URL de callback informativa: hay que registrarla tal cual en el panel de Strava (strava.com/settings/api).
    $callbackurl = (new moodle_url('/local/stravaauth/callback.php'))->out(false);
    $settings->add(new admin_setting_heading(
        'local_stravaauth/callbackinfo',
        '',
        get_string('callbackurl_desc', 'local_stravaauth', $callbackurl)
    ));
}
