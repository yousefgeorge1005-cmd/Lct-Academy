<?php
/**
 * LCT Academy — Cloudinary upload helper.
 * Uses Cloudinary's signed upload API directly over cURL (no SDK/Composer needed).
 *
 * Every function here is defensive on purpose: this runs in the middle of a
 * customer's booking submission, so a network hiccup, a missing PHP
 * extension, or a Cloudinary-side error must NEVER crash the booking. Any
 * failure just returns null, and the caller (handle_id_photo_upload in
 * functions.php) falls back to keeping the photo on the server instead.
 */

function _cloudinary_credentials() {
    if (function_exists('upstash_is_configured') && upstash_is_configured()) {
        $c = upstash_get('lct:cloudinary_credentials');
        if (is_array($c) && !empty($c['cloud_name']) && !empty($c['api_key'])) {
            return $c;
        }
    }
    if (!defined('CLOUDINARY_CREDENTIALS_FILE') || !file_exists(CLOUDINARY_CREDENTIALS_FILE)) return null;
    $creds = json_decode(file_get_contents(CLOUDINARY_CREDENTIALS_FILE), true);
    if (!is_array($creds) || empty($creds['cloud_name']) || empty($creds['api_key']) || empty($creds['api_secret'])) return null;
    return $creds;
}


/**
 * Upload a local file to Cloudinary.
 * Returns the secure (https) delivery URL on success, or null on ANY failure
 * (missing extensions, network issues, bad credentials, Cloudinary-side errors).
 * On success only, the local $filePath is deleted.
 */
function upload_to_cloudinary($filePath, $publicId) {
    try {
        if (!function_exists('curl_init')) return null;
        if (!file_exists($filePath)) return null;

        $creds = _cloudinary_credentials();
        if (!$creds) return null;

        $timestamp = time();
        // Only params that are part of the signature go here (alphabetical order is required).
        $signParams = [
            'folder'    => 'lct-academy-id-photos',
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];
        ksort($signParams);
        $toSign = '';
        foreach ($signParams as $k => $v) {
            $toSign .= ($toSign === '' ? '' : '&') . $k . '=' . $v;
        }
        $signature = sha1($toSign . $creds['api_secret']);

        $postFields = $signParams + [
            'api_key'   => $creds['api_key'],
            'signature' => $signature,
            'file'      => new CURLFile($filePath),
        ];

        $ch = curl_init("https://api.cloudinary.com/v1_1/{$creds['cloud_name']}/image/upload");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $resp  = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno !== 0 || $resp === false) return null;
        $data = json_decode($resp, true);
        if (!is_array($data) || empty($data['secure_url'])) return null;

        @unlink($filePath);

        return $data['secure_url'];
    } catch (\Throwable $e) {
        // Never let a Cloudinary/network problem take down a booking submission.
        return null;
    }
}
