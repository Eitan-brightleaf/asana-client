<?php

namespace BrightleafDigital\Utils;

use Exception;

/**
 * CryptoUtils provides basic password-based encryption and decryption utilities.
 * ⚠️ For development use only. Not recommended for production.
 */
class CryptoUtils
{
    /**
     * Default cipher to use for encryption/decryption.
     * AES-256-GCM provides authenticated encryption.
     */
    public const DEFAULT_CIPHER = 'aes-256-gcm';

    // Number of bytes for the random salt.
    private const SALT_BYTES = 16;
    // Number of PBKDF2 iterations for key derivation.
    private const PBKDF2_ITERS = 100_000;
    // Length of the derived key (32 bytes = 256 bits).
    private const KEY_LENGTH = 32;
    // Length of the GCM authentication tag (16 bytes = 128 bits).
    private const TAG_LENGTH = 16;

    /**
     * Encrypts the given plaintext using PBKDF2 key derivation and AES-GCM authenticated encryption.
     *
     * @param string $plaintext The data to be encrypted.
     * @param string $password The password used to derive the encryption key.
     *
     * @return string The base64-encoded encrypted string containing salt, IV, ciphertext, and authentication tag.
     * Format: [salt (16 bytes)] [IV (variable)] [ciphertext] [tag (16 bytes)]

     * @throws Exception If required OpenSSL functions are unavailable or encryption fails.
     */
    public static function encrypt(string $plaintext, string $password): string
    {
        // Ensure required OpenSSL functions are available.
        if (!function_exists('openssl_encrypt') || !function_exists('openssl_pbkdf2')) {
            throw new Exception('Required OpenSSL functions are not available.');
        }
        // Generate a random salt for PBKDF2.
        $salt = random_bytes(self::SALT_BYTES);
        // Derive a symmetric encryption key from the password and salt.
        $encKey = openssl_pbkdf2($password, $salt, self::KEY_LENGTH, self::PBKDF2_ITERS, 'sha256');
        if ($encKey === false) {
            throw new Exception('Encryption key derivation failed.');
        }
        // Generate a random IV (nonce) for AES-GCM.
        $iv = random_bytes(openssl_cipher_iv_length(self::DEFAULT_CIPHER));
        $tag = '';
        // Encrypt the plaintext using AES-256-GCM.
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::DEFAULT_CIPHER,
            $encKey,
            // Use OPENSSL_RAW_DATA to get raw binary output (not base64-encoded)
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );
        // Check for encryption failure.
        if ($ciphertext === false) {
            throw new Exception('Encryption failed.');
        }
        // Concatenate salt, IV, ciphertext, and tag, then base64-encode for storage/transmission.
        return base64_encode($salt . $iv . $ciphertext . $tag);
    }

    /**
     * Decrypts data encrypted using the encrypt() method with PBKDF2 and AES-GCM.
     *
     * @param string $data The base64-encoded encrypted string containing salt, IV, ciphertext, and authentication tag.
     * @param string $password The password used to derive the decryption key.
     *
     * @return string The decrypted plaintext data.
     * @throws Exception If required OpenSSL functions are unavailable, data is invalid, or decryption fails.
     */
    public static function decrypt(string $data, string $password): string
    {
        // Ensure required OpenSSL functions are available.
        if (!function_exists('openssl_decrypt') || !function_exists('openssl_pbkdf2')) {
            throw new Exception('Required OpenSSL functions are not available.');
        }
        // Decode the base64-encoded input.
        $decoded = base64_decode($data, true);
        // Get IV length for the cipher.
        $ivLen = openssl_cipher_iv_length(self::DEFAULT_CIPHER);
        // Minimum length: salt + IV + tag (ciphertext must be at least 1 byte).
        $minLength = self::SALT_BYTES + $ivLen + self::TAG_LENGTH;
        // Validate decoded data length.
        if ($decoded === false || strlen($decoded) < $minLength) {
            throw new Exception('Invalid or truncated data.');
        }
        // Extract salt from the beginning.
        $salt = substr($decoded, 0, self::SALT_BYTES);
        // Extract IV after the salt.
        $iv = substr($decoded, self::SALT_BYTES, $ivLen);
        // Extract authentication tag from the end.
        $tag = substr($decoded, -self::TAG_LENGTH);
        // Extract ciphertext (between IV and tag).
        $ciphertext = substr($decoded, self::SALT_BYTES + $ivLen, -self::TAG_LENGTH);
        // Derive the decryption key using PBKDF2.
        $encKey = openssl_pbkdf2($password, $salt, self::KEY_LENGTH, self::PBKDF2_ITERS, 'sha256');
        if ($encKey === false) {
            throw new Exception('Encryption key derivation failed.');
        }
        // Attempt to decrypt the ciphertext.
        $plaintext = openssl_decrypt(
            $ciphertext,
            self::DEFAULT_CIPHER,
            $encKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        // Check for decryption/authentication failure.
        if ($plaintext === false) {
            throw new Exception('Decryption failed or data tampered.');
        }
        return $plaintext;
    }
}
