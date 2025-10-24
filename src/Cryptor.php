<?php

declare(strict_types=1);

namespace Lazervel\Cryptor;

/**
 * Class Cryptor
 *
 * Provides AES-256-GCM encryption and decryption functionality with
 * authenticated encryption, key derivation, and optional AAD (Additional Authenticated Data) support.
 *
 * @package Lazervel\Cryptor
 */
class Cryptor
{
  /**
   * The 32-byte encryption key derived from the application key or provided key.
   *
   * @var string
   */
  private string $key;

  /**
   * Create a new Cryptor instance.
   * 
   * @param string|null $key [optional]
   * @throws \RuntimeException If no valid encryption key is found.
   */
  public function __construct(?string $key = null)
  {
    $raw = $key ?? ($_ENV['APP_KEY'] ?? '');
    if ($raw === '') {
      throw new \RuntimeException('Encryption [APP_KEY] key not found in environment.');
    }

    // Ensure 32-byte key for AES-256 (derive from passphrase safely)
    $this->key = \hash('sha256', $raw, true);
  }

  /**
   * Encrypts plain text data using AES-256-GCM or the specified cipher.
   * 
   * @param string $data        [required]
   * @param string|null $cipher [optional]
   * @param string $add         [optional]
   * 
   * @return string|false Base64-encoded encrypted payload on success, or false on failure.
   * @throws \InvalidArgumentException If the selected cipher is invalid.
   */
  public function encrypt(string $data, ?string $cipher = null, string $add = '')
  {
    $tag = '';
    $cipher = $cipher ?? 'aes-256-gcm';
    $ivLen = \openssl_cipher_iv_length($cipher);

    if ($ivLen === false || $ivLen <= 0) {
      throw new \InvalidArgumentException('Invalid cipher selected.');
    }

    $iv = \random_bytes($ivLen);
    $encrypted = \openssl_encrypt($data, $cipher, $this->key, \OPENSSL_RAW_DATA, $iv, $tag, $add);

    // In-Case failed encryption
    if (!$encrypted) return false;

    return \base64_encode(\json_encode([
      'iv' => \base64_encode($iv),
      'value' => \base64_encode($encrypted),
      'cipher' => \base64_encode($cipher),
      'tag' => \base64_encode($tag ?? '')
    ]));
  }

  /**
   * Verifies whether the given encrypted value matches the provided plain text.
   * 
   * @param string $plain     [required]
   * @param string $encrypted [required]
   * @param string $add       [optional]
   * 
   * @return bool True if verification succeeds (data matches), false otherwise.
   */
  public function verify(string $plain, string $encrypted, string $add = '') : bool
  {
    $data = $this->decrypt($encrypted, $add);
    return $data !== false && \hash_equals($data, $plain);
  }

  /**
   * Decrypts encrypted data previously encrypted with {@see encrypt()}.
   * 
   * @param string $data [required]
   * @param string $add  [optional]
   * 
   * @return string|false The decrypted plain text on success, or false if decryption fails.
   */
  public function decrypt(string $data, string $add = '')
  {
    $data = \json_decode(\base64_decode($data), true);

    if (!\is_array($data) || !isset($data['iv'], $data['value'], $data['cipher'])) {
      return false;
    }
      
    $tag = isset($data['tag']) ? base64_decode($data['tag']) : '';
    $value = \base64_decode($data['value']);
    $iv = \base64_decode($data['iv']);
    $cipher = \base64_decode($data['cipher']);

    return \openssl_decrypt($value, $cipher, $this->key, \OPENSSL_RAW_DATA, $iv, $tag, $add);
  }

  /**
   * Prevents serialization of sensitive data.
   *
   * @return array An empty array to avoid exposing sensitive information.
   */
  public function __sleep() : array
  {
    return [];
  }

  /**
   * Controls what is displayed during debugging (e.g., var_dump()).
   *
   * @return array An array hiding the encryption key from output.
   */
  public function __debugInfo(): array
  {
    return ['key' => ''];
  }

  /**
   * Destructor to securely erase the encryption key from memory.
   */
  public function __destruct()
  {
    $this->key = '';
  }
}
?>