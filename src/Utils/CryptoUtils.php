<?php

namespace BrightleafDigital\Utils;

use Exception;

class CryptoUtils
{
    /**
     * Encrypts the given data using an encryption key derived from the provided salt.
     * The encrypted data includes a MAC for integrity verification.
     *
     * @param string $data The data to be encrypted.
     * @param string $salt A unique string used to derive the encryption and MAC keys.
     *
     * @return string The base64-encoded encrypted string containing the MAC, nonce, and ciphertext.
     * @throws Exception If the OpenSSL extension is unavailable or encryption fails.
     */
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


    /**
     * Decrypts the given data using a decryption key derived from the provided salt.
     * Verifies the integrity of the data using a MAC before decryption.
     *
     * @param string $data The base64-encoded encrypted string containing the MAC, nonce, and ciphertext.
     * @param string $salt A unique string used to derive the decryption and MAC keys.
     *
     * @return string The decrypted plaintext data.
     * @throws Exception If the OpenSSL extension is unavailable, MAC verification fails, or decryption fails.
     */
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
