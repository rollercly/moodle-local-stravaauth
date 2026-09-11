<?php
// This file is part of Moodle - http://moodle.org/

namespace local_stravaauth;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');

/**
 * Cliente OAuth2 + wrapper minimo de la API v3 de Strava.
 *
 * Nota sobre la URL base: developers.strava.com anuncio en su changelog que
 * https://www.strava.com/api/v3 pasara a https://api-v3.strava.com a partir
 * del 4 de enero de 2027. Se centraliza aqui para cambiarla en un solo sitio.
 */
class api_client {

    /** @var string URL base de la API v3 (revisar antes de enero de 2027). */
    const API_BASE = 'https://www.strava.com/api/v3';

    /** @var string Endpoint de autorizacion OAuth2. */
    const AUTHORIZE_URL = 'https://www.strava.com/oauth/authorize';

    /** @var string Endpoint de intercambio/refresco de tokens. */
    const TOKEN_URL = 'https://www.strava.com/oauth/token';

    /**
     * Construye la URL a la que redirigir al usuario para autorizar la app.
     *
     * @param string $state normalmente sesskey() para validar el retorno.
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
     * Intercambia el codigo de autorizacion por un access_token/refresh_token
     * y los guarda (o actualiza) para el usuario indicado.
     *
     * @param int $userid
     * @param string $code codigo devuelto por Strava en el callback.
     * @return \stdClass registro guardado en local_stravaauth_token.
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
     * Devuelve un access_token valido para el usuario, refrescandolo si ha
     * caducado. Lanza excepcion si el usuario no ha vinculado su cuenta.
     *
     * @param int $userid
     * @return string access_token valido.
     */
    public static function get_valid_access_token(int $userid): string {
        global $DB;

        $token = $DB->get_record('local_stravaauth_token', ['userid' => $userid]);
        if (!$token) {
            throw new \moodle_exception('errornotconnected', 'local_stravaauth');
        }

        // Margen de 5 minutos para evitar condiciones de carrera con la caducidad.
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
     * Llamada GET generica autenticada contra la API de Strava.
     *
     * @param int $userid usuario cuyo token se usa para autenticar.
     * @param string $endpoint p.ej. 'athlete/activities'.
     * @param array $params query string adicional.
     * @return array respuesta decodificada de JSON.
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
     * Indica si el usuario ya ha vinculado su cuenta de Strava.
     */
    public static function is_connected(int $userid): bool {
        global $DB;
        return $DB->record_exists('local_stravaauth_token', ['userid' => $userid]);
    }

    /**
     * Inserta o actualiza el registro de token para el usuario.
     *
     * @param int $userid
     * @param array $data respuesta cruda del endpoint de token de Strava.
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
