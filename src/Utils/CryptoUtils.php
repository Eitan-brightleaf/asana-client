<?php

namespace BrightleafDigital\Utils;

use Exception;

class CryptoUtils
{
    public static function encrypt(string $data, string $salt): string
    {
        if (! function_exists('openssl_encrypt')) {
            throw new Exception('OpenSSL extension is not available.');
        }
        $encryptionKey = 'AsanaClientEncryptionKey_' . $salt;
        $macKey = 'AsanaClientMacKey_' . $salt;

        $nonce = random_bytes(16); // Generate a secure nonce (IV).
        $cipherName = 'aes-256-ctr'; // Specify the encryption cipher.
        $options = OPENSSL_RAW_DATA;

        $ciphertext = openssl_encrypt($data, $cipherName, $encryptionKey, $options, $nonce);

        if ($ciphertext === false) {
            throw new Exception('Encryption failed.');
        }

        // Generate a MAC for integrity verification.
        $mac = hash_hmac('sha512', $nonce . $ciphertext, $macKey, true);

        // Combine the MAC, nonce, and ciphertext into a single encoded string.
        return base64_encode($mac . $nonce . $ciphertext);
    }

    
    public static function decrypt(string $data, string $salt): string
    {
        if (! function_exists('openssl_decrypt')) {
            throw new Exception('OpenSSL extension is not available.');
        }
        
        $encryptionKey = 'AsanaClientEncryptionKey_' . $salt;
        $macKey = 'AsanaClientMacKey_' . $salt;

        // Decode the base64-encoded data.
        $decodedData = base64_decode($data);

        // Extract the MAC, nonce, and ciphertext from the decoded data.
        $mac = substr($decodedData, 0, 64);
        $nonce = substr($decodedData, 64, 16);
        $ciphertext = substr($decodedData, 80);

        // Verify the MAC for integrity.
        if (! hash_equals(hash_hmac('sha512', $nonce . $ciphertext, $macKey, true), $mac)) {
            throw new Exception('MAC verification failed.');
        }

        // Decrypt the ciphertext.
        $cipherName = 'aes-256-ctr'; // Specify the encryption cipher.
        $options = OPENSSL_RAW_DATA;

        $plaintext = openssl_decrypt($ciphertext, $cipherName, $encryptionKey, $options, $nonce);

        if ($plaintext === false) {
            throw new Exception('Decryption failed.');
        }

        return $plaintext;
    }
}