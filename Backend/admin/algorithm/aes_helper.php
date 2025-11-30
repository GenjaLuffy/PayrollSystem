<?php
// AES Helper for encrypting/decrypting sensitive data using AES-256-GCM
define('AES_KEY', '12345678901234567890123456789012'); // 32 chars

function encryptAES_GCM($plaintext) {
    $key = AES_KEY;
    $iv = random_bytes(12); // 12 bytes IV for GCM
    $tag = '';
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return base64_encode($iv . $tag . $ciphertext); // store IV + TAG + ciphertext
}

function decryptAES_GCM($ciphertext_base64) {
    $key = AES_KEY;
    $data = base64_decode($ciphertext_base64);
    if (strlen($data) < 28) return null; // IV + TAG + ciphertext minimum
    $iv = substr($data, 0, 12);
    $tag = substr($data, 12, 16);
    $ciphertext = substr($data, 28);
    return openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
}
?>
