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
 * Strava API client and OAuth2 token handling.
 *
 * @package   local_stravaauth
 * @copyright 2026 Jose Lorenzo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stravaauth;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');

/**
 * OAuth2 client and minimal wrapper of the Strava API v3.
 *
 * Note on the base URL: developers.strava.com announced in its changelog that
 * https://www.strava.com/api/v3 will become https://api-v3.strava.com from
 * 4 January 2027. It is centralised here so it can be changed in one place.
 */
class api_client {

    /** @var string Base URL of the API v3 (review before January 2027). */
    const API_BASE = 'https://www.strava.com/api/v3';

    /** @var string OAuth2 authorisation endpoint. */
    const AUTHORIZE_URL = 'https://www.strava.com/oauth/authorize';

    /** @var string Token exchange/refresh endpoint. */
    const TOKEN_URL = 'https://www.strava.com/oauth/token';

    /**
     * Builds the URL the user is redirected to in order to authorise the app.
     *
     * @param string $state usually sesskey(), used to validate the return.
     * @return string
     */
    public static function get_authorize_url(string $state): string {
        global $CFG;

        $params = [
            'client_id'       => get_config('local_stravaauth', 'clientid'),
            'redirect_uri'    => (new \moodle_url('/local/stravaauth/callback.php'))->out(false),
            'response_type'   => 'code',
            'approval_prompt' => 'auto',
            'scope'           => 'read,activity:read_all',
            'state'           => $state,
        ];

        return self::AUTHORIZE_URL . '?' . http_build_query($params);
    }

    /**
     * Exchanges the authorisation code for an access_token/refresh_token
     * and stores (or updates) them for the given user.
     *
     * @param int $userid
     * @param string $code code returned by Strava in the callback.
     * @return \stdClass record stored in local_stravaauth_token.
     */
    public static function exchange_code(int $userid, string $code): \stdClass {
        $curl = new \curl();
        $response = $curl->post(self::TOKEN_URL, [
            'client_id'     => get_config('local_stravaauth', 'clientid'),
            'client_secret' => get_config('local_stravaauth', 'clientsecret'),
            'code'          => $code,
            'grant_type'    => 'authorization_code',
        ]);

        $data = json_decode($response, true);
        if (empty($data['access_token'])) {
            throw new \moodle_exception('errortokenexchange', 'local_stravaauth', '', null,
                $response);
        }

        return self::store_token($userid, $data);
    }

    /**
     * Returns a valid access_token for the user, refreshing it if it has
     * expired. Throws an exception if the user has not linked their account.
     *
     * @param int $userid
     * @return string valid access_token.
     */
    public static function get_valid_access_token(int $userid): string {
        global $DB;

        $token = $DB->get_record('local_stravaauth_token', ['userid' => $userid]);
        if (!$token) {
            throw new \moodle_exception('errornotconnected', 'local_stravaauth');
        }

        // 5-minute margin to avoid race conditions with token expiry.
        if ($token->expiresat > (time() + 300)) {
            return $token->accesstoken;
        }

        $curl = new \curl();
        $response = $curl->post(self::TOKEN_URL, [
            'client_id'     => get_config('local_stravaauth', 'clientid'),
            'client_secret' => get_config('local_stravaauth', 'clientsecret'),
            'refresh_token' => $token->refreshtoken,
            'grant_type'    => 'refresh_token',
        ]);

        $data = json_decode($response, true);
        if (empty($data['access_token'])) {
            throw new \moodle_exception('errortokenrefresh', 'local_stravaauth', '', null,
                $response);
        }

        $updated = self::store_token($userid, $data);
        return $updated->accesstoken;
    }

    /**
     * Generic authenticated GET call to the Strava API.
     *
     * @param int $userid user whose token is used to authenticate.
     * @param string $endpoint e.g. 'athlete/activities'.
     * @param array $params additional query string.
     * @return array decoded JSON response.
     */
    public static function get(int $userid, string $endpoint, array $params = []): array {
        $accesstoken = self::get_valid_access_token($userid);

        $curl = new \curl();
        $curl->setHeader(['Authorization: Bearer ' . $accesstoken]);

        $url = self::API_BASE . '/' . ltrim($endpoint, '/');
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $response = $curl->get($url);
        $info = $curl->get_info();

        if (!empty($info['http_code']) && $info['http_code'] >= 400) {
            throw new \moodle_exception('errorapicall', 'local_stravaauth', '', null, $response);
        }

        return json_decode($response, true) ?: [];
    }

    /**
     * Whether the user has already linked their Strava account.
     */
    public static function is_connected(int $userid): bool {
        global $DB;
        return $DB->record_exists('local_stravaauth_token', ['userid' => $userid]);
    }

    /**
     * Inserts or updates the user's token record.
     *
     * @param int $userid
     * @param array $data raw response of the Strava token endpoint.
     * @return \stdClass
     */
    private static function store_token(int $userid, array $data): \stdClass {
        global $DB;

        $now = time();
        $existing = $DB->get_record('local_stravaauth_token', ['userid' => $userid]);

        $record = new \stdClass();
        $record->userid       = $userid;
        $record->athleteid    = $data['athlete']['id'] ?? ($existing->athleteid ?? 0);
        $record->accesstoken  = $data['access_token'];
        $record->refreshtoken = $data['refresh_token'];
        $record->expiresat    = $data['expires_at'];
        $record->scope        = $data['scope'] ?? ($existing->scope ?? null);
        $record->timemodified = $now;

        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record('local_stravaauth_token', $record);
        } else {
            $record->timecreated = $now;
            $record->id = $DB->insert_record('local_stravaauth_token', $record);
        }

        return $record;
    }
}
