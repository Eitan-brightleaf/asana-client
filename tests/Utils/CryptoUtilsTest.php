<?php

namespace BrightleafDigital\Tests\Utils;

use BrightleafDigital\Utils\CryptoUtils;
use Exception;
use PHPUnit\Framework\TestCase;

class CryptoUtilsTest extends TestCase
{
    /**
     * Test that encrypt/decrypt round trip returns the original data.
     */
    public function testEncryptDecryptRoundTrip(): void
    {
        $plaintext = 'This is a secret message';
        $password = 'test_password_123';

        $encrypted = CryptoUtils::encrypt($plaintext, $password);
        $decrypted = CryptoUtils::decrypt($encrypted, $password);

        $this->assertSame($plaintext, $decrypted);
    }

    /**
     * Test encryption produces different outputs with different passwords.
     */
    public function testDifferentPasswordsDifferentOutput(): void
    {
        $plaintext = 'Same message';
        $password1 = 'password_one';
        $password2 = 'password_two';

        $encrypted1 = CryptoUtils::encrypt($plaintext, $password1);
        $encrypted2 = CryptoUtils::encrypt($plaintext, $password2);

        $this->assertNotSame($encrypted1, $encrypted2);
    }

    /**
     * Test encryption produces different outputs even with same password (due to random IV/salt).
     */
    public function testSamePasswordDifferentOutput(): void
    {
        $plaintext = 'Same message';
        $password = 'same_password';

        $encrypted1 = CryptoUtils::encrypt($plaintext, $password);
        $encrypted2 = CryptoUtils::encrypt($plaintext, $password);

        // Due to random salt and IV, outputs should differ
        $this->assertNotSame($encrypted1, $encrypted2);

        // But both should decrypt to the same value
        $this->assertSame($plaintext, CryptoUtils::decrypt($encrypted1, $password));
        $this->assertSame($plaintext, CryptoUtils::decrypt($encrypted2, $password));
    }

    /**
     * Test decryption fails with wrong password.
     */
    public function testDecryptWithWrongPasswordFails(): void
    {
        $plaintext = 'Secret data';
        $correctPassword = 'correct_password';
        $wrongPassword = 'wrong_password';

        $encrypted = CryptoUtils::encrypt($plaintext, $correctPassword);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Decryption failed or data tampered');

        CryptoUtils::decrypt($encrypted, $wrongPassword);
    }

    /**
     * Test decryption fails with invalid base64 data.
     */
    public function testDecryptWithInvalidBase64Fails(): void
    {
        $password = 'test_password';
        $invalidData = 'not_valid_base64!!!';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid or truncated data');

        CryptoUtils::decrypt($invalidData, $password);
    }

    /**
     * Test decryption fails with truncated data.
     */
    public function testDecryptWithTruncatedDataFails(): void
    {
        $password = 'test_password';
        // Create valid base64 but too short to contain salt + IV + ciphertext + tag
        $truncatedData = base64_encode('short');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid or truncated data');

        CryptoUtils::decrypt($truncatedData, $password);
    }

    /**
     * Test encryption works with empty string.
     */
    public function testEncryptEmptyString(): void
    {
        $plaintext = '';
        $password = 'test_password';

        $encrypted = CryptoUtils::encrypt($plaintext, $password);
        $decrypted = CryptoUtils::decrypt($encrypted, $password);

        $this->assertSame($plaintext, $decrypted);
    }

    /**
     * Test encryption works with long string.
     */
    public function testEncryptLongString(): void
    {
        $plaintext = str_repeat('A', 10000);
        $password = 'test_password';

        $encrypted = CryptoUtils::encrypt($plaintext, $password);
        $decrypted = CryptoUtils::decrypt($encrypted, $password);

        $this->assertSame($plaintext, $decrypted);
    }

    /**
     * Test encryption works with special characters.
     */
    public function testEncryptSpecialCharacters(): void
    {
        $plaintext = "Special chars: !@#$%^&*()_+-=[]{}|;':\",./<>?`~\n\t\r";
        $password = 'test_password';

        $encrypted = CryptoUtils::encrypt($plaintext, $password);
        $decrypted = CryptoUtils::decrypt($encrypted, $password);

        $this->assertSame($plaintext, $decrypted);
    }

    /**
     * Test encryption works with Unicode characters.
     */
    public function testEncryptUnicodeCharacters(): void
    {
        $plaintext = 'Unicode: 日本語 中文 한국어 🎉🔐';
        $password = 'test_password';

        $encrypted = CryptoUtils::encrypt($plaintext, $password);
        $decrypted = CryptoUtils::decrypt($encrypted, $password);

        $this->assertSame($plaintext, $decrypted);
    }

    /**
     * Test encryption output is base64 encoded.
     */
    public function testEncryptOutputIsBase64(): void
    {
        $plaintext = 'Test message';
        $password = 'test_password';

        $encrypted = CryptoUtils::encrypt($plaintext, $password);

        // Check it's valid base64 by decoding and re-encoding
        $decoded = base64_decode($encrypted, true);
        $this->assertNotFalse($decoded);
        $this->assertSame($encrypted, base64_encode($decoded));
    }

    /**
     * Test that DEFAULT_CIPHER constant is set correctly.
     */
    public function testDefaultCipherConstant(): void
    {
        $this->assertSame('aes-256-gcm', CryptoUtils::DEFAULT_CIPHER);
    }

    /**
     * Test decryption fails when data is tampered with.
     */
    public function testDecryptionFailsWithTamperedData(): void
    {
        $plaintext = 'Original message';
        $password = 'test_password';

        $encrypted = CryptoUtils::encrypt($plaintext, $password);

        // Tamper with the encrypted data
        $decoded = base64_decode($encrypted);
        // Flip a bit in the middle of the ciphertext
        $midpoint = (int) (strlen($decoded) / 2);
        $decoded[$midpoint] = chr(ord($decoded[$midpoint]) ^ 0xFF);
        $tampered = base64_encode($decoded);

        $this->expectException(Exception::class);

        CryptoUtils::decrypt($tampered, $password);
    }

    /**
     * Test with various password lengths.
     */
    public function testVariousPasswordLengths(): void
    {
        $plaintext = 'Test message';

        $passwords = [
            'a',                          // 1 char
            'ab',                         // 2 chars
            'short',                      // 5 chars
            'medium_password',            // 15 chars
            str_repeat('x', 50),          // 50 chars
            str_repeat('long', 100),      // 400 chars
        ];

        foreach ($passwords as $password) {
            $encrypted = CryptoUtils::encrypt($plaintext, $password);
            $decrypted = CryptoUtils::decrypt($encrypted, $password);
            $this->assertSame($plaintext, $decrypted, "Failed for password length: " . strlen($password));
        }
    }

    /**
     * Test with empty password.
     */
    public function testEmptyPassword(): void
    {
        $plaintext = 'Test message';
        $password = '';

        $encrypted = CryptoUtils::encrypt($plaintext, $password);
        $decrypted = CryptoUtils::decrypt($encrypted, $password);

        $this->assertSame($plaintext, $decrypted);
    }

    /**
     * Test with password containing special characters.
     */
    public function testPasswordWithSpecialCharacters(): void
    {
        $plaintext = 'Test message';
        $password = 'P@$$w0rd!#%^&*()_+-=[]{}|;\':",./<>?`~';

        $encrypted = CryptoUtils::encrypt($plaintext, $password);
        $decrypted = CryptoUtils::decrypt($encrypted, $password);

        $this->assertSame($plaintext, $decrypted);
    }
}
