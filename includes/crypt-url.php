<?php if (!defined('ABSPATH')) { exit(); }
switch ($action) {
case 'decrypt':
if (strstr($url, '?plugin=contact-manager&url=')) {
$url = explode('?plugin=contact-manager&url=', $url);
$url = $url[1];
$url = base64_decode($url);
if (function_exists('openssl_decrypt')) {
$hash = contact_hash(contact_data('encrypted_urls_key'));
$url = openssl_decrypt($url, 'AES-256-CTR', $hash, OPENSSL_RAW_DATA, substr($hash, 0, openssl_cipher_iv_length('AES-256-CTR'))); }
$url = explode('|', trim($url));
$T = $url[0];
$url = $url[1];
$S = time() - $T;
if ($S > 3600*contact_data('encrypted_urls_validity_duration')) { $url = HOME_URL; } }
else { $url = HOME_URL; } break;
case 'encrypt':
$url = time().'|'.$url;
if (function_exists('openssl_encrypt')) {
$hash = contact_hash(contact_data('encrypted_urls_key'));
$url = openssl_encrypt($url, 'AES-256-CTR', $hash, OPENSSL_RAW_DATA, substr($hash, 0, openssl_cipher_iv_length('AES-256-CTR'))); }
$url = base64_encode($url);
$url = HOME_URL.'?plugin=contact-manager&url='.$url; }