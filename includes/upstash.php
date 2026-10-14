<?php
/**
 * LCT Academy — Upstash Redis REST Client.
 * Serverless-native storage handler for Vercel and cloud deployments.
 */

if (!defined('UPSTASH_REDIS_REST_URL')) {
    define('UPSTASH_REDIS_REST_URL', getenv('UPSTASH_REDIS_REST_URL') ?: 'https://relieved-magpie-171147.upstash.io');
}

if (!defined('UPSTASH_REDIS_REST_TOKEN')) {
    define('UPSTASH_REDIS_REST_TOKEN', getenv('UPSTASH_REDIS_REST_TOKEN') ?: 'gQAAAAAAApyLAAIgcDFiZWM0MDAxZmEyMmQ0MGUzYmI0NjQ3OTA3ZGRmNzU2NQ');
}

function upstash_is_configured() {
    return defined('UPSTASH_REDIS_REST_URL') 
        && !empty(UPSTASH_REDIS_REST_URL) 
        && defined('UPSTASH_REDIS_REST_TOKEN') 
        && !empty(UPSTASH_REDIS_REST_TOKEN);
}

/**
 * Execute an arbitrary Redis command via Upstash REST API.
 * @param array $command e.g. ['GET', 'mykey'] or ['SET', 'mykey', 'myvalue']
 * @return array|null Returns ['result' => ...] or null on failure
 */
function upstash_command(array $command) {
    if (!upstash_is_configured()) return null;

    $url = rtrim(UPSTASH_REDIS_REST_URL, '/');
    $token = UPSTASH_REDIS_REST_TOKEN;
    $body = json_encode($command, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            return null;
        }
        return json_decode($response, true);
    }

    // Fallback to file_get_contents stream context
    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Authorization: Bearer {$token}\r\nContent-Type: application/json\r\n",
            'content' => $body,
            'timeout' => 8,
        ]
    ];
    $ctx = stream_context_create($opts);
    $response = @file_get_contents($url, false, $ctx);
    if ($response === false) return null;
    return json_decode($response, true);
}

/** Get and decode JSON value from Upstash */
function upstash_get($key, $default = null) {
    $res = upstash_command(['GET', $key]);
    if (!isset($res['result']) || $res['result'] === null) {
        return $default;
    }
    $raw = $res['result'];
    $data = is_string($raw) ? json_decode($raw, true) : $raw;
    return is_null($data) ? $raw : $data;
}

/** Encode and store JSON value into Upstash */
function upstash_set($key, $val) {
    $str = is_string($val) ? $val : json_encode($val, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $res = upstash_command(['SET', $key, $str]);
    return isset($res['result']) && $res['result'] === 'OK';
}

/** Delete a key */
function upstash_del($key) {
    $res = upstash_command(['DEL', $key]);
    return isset($res['result']);
}

/** Add member to set */
function upstash_sadd($key, $member) {
    $res = upstash_command(['SADD', $key, (string)$member]);
    return isset($res['result']);
}

/** Remove member from set */
function upstash_srem($key, $member) {
    $res = upstash_command(['SREM', $key, (string)$member]);
    return isset($res['result']);
}

/** Get all members in set */
function upstash_smembers($key) {
    $res = upstash_command(['SMEMBERS', $key]);
    if (!isset($res['result']) || !is_array($res['result'])) {
        return [];
    }
    return $res['result'];
}

/** Batch get and decode multiple keys in ONE single HTTP request */
function upstash_mget(array $keys) {
    if (empty($keys) || !upstash_is_configured()) return [];
    $cmd = array_merge(['MGET'], array_values($keys));
    $res = upstash_command($cmd);
    if (!isset($res['result']) || !is_array($res['result'])) return [];
    $out = [];
    foreach ($res['result'] as $raw) {
        if ($raw === null) continue;
        $data = is_string($raw) ? json_decode($raw, true) : $raw;
        if ($data && is_array($data)) $out[] = $data;
    }
    return $out;
}

