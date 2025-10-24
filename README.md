# 🔐 Lazervel Cryptor

A lightweight and secure PHP encryption library that provides modern **AES-256-GCM** authenticated encryption and decryption with optional AAD (Additional Authenticated Data) support.  
It is designed to be **simple, dependency-free**, and compatible with any PHP application or framework.

---

## 🚀 Features

- ✅ AES-256-GCM authenticated encryption  
- ✅ Optional AAD (Additional Authenticated Data)  
- ✅ Secure key handling & memory cleanup  
- ✅ JSON + Base64 encoded output  
- ✅ Key derived safely from `APP_KEY` or custom string  
- ✅ No framework dependency (works in plain PHP or Laravel)  

---

## 📦 Installation

Use Composer (recommended):

```bash
composer require lazervel/cryptor
```

Or manually include it:

```php
require_once 'src/Cryptor.php';
```

---

## ⚙️ Environment Setup

Set your application key in `.env` or environment variables:

```env
APP_KEY=base64:your-secret-key
```

Alternatively, you can provide a custom key directly when creating an instance.

---

## 🧠 Basic Usage

```php
<?php

use Lazervel\Cryptor\Cryptor;

// Create instance (uses APP_KEY from env if not provided)
$cryptor = new Cryptor('my-secret-key');

// Encrypt a message
$encrypted = $cryptor->encrypt('Hello World!');
echo "Encrypted: " . $encrypted . PHP_EOL;

// Decrypt the message
$decrypted = $cryptor->decrypt($encrypted);
echo "Decrypted: " . $decrypted . PHP_EOL;

// Verify that data matches
if ($cryptor->verify('Hello World!', $encrypted)) {
    echo "✅ Data verified successfully!";
} else {
    echo "❌ Verification failed!";
}
```

---

## 🧩 With Additional Authenticated Data (AAD)

You can attach additional data (not encrypted but authenticated):

```php
$add = 'payment#RZP123'; // example reference

$encrypted = $cryptor->encrypt('Sensitive Transaction Data', 'aes-256-gcm', $add);

// Must use same $add while decrypting
$decrypted = $cryptor->decrypt($encrypted, $add);
```

If the `$add` differs, decryption will fail — ensuring data integrity.

---

## 🛡️ Security Design

| Aspect | Detail |
|--------|---------|
| **Algorithm** | AES-256-GCM (Authenticated Encryption) |
| **IV** | Generated securely via `random_bytes()` |
| **Tag** | Auto-generated and verified internally |
| **Key Derivation** | `hash('sha256', $raw, true)` ensures 32-byte AES key |
| **Memory Safety** | Key wiped in destructor (`__destruct()`) |
| **Serialization Protection** | `__sleep()` prevents exposing secrets |
| **Debug Protection** | `__debugInfo()` hides the key during dumps |

---

## ⚠️ Error Handling

| Error | Thrown when |
|-------|--------------|
| `RuntimeException` | No key found in environment |
| `InvalidArgumentException` | Unsupported cipher name |
| `false` return | Encryption/decryption failure |

You can wrap encryption/decryption calls inside `try/catch` if desired:

```php
try {
    $cryptor = new Cryptor();
    $data = $cryptor->decrypt($input);
} catch (RuntimeException $e) {
    echo $e->getMessage();
}
```

---

## 🧰 Supported Ciphers

| Cipher | Description |
|---------|--------------|
| `aes-256-gcm` | (Default) Modern authenticated encryption |
| `aes-128-gcm` | Lightweight variant |
| `aes-256-cbc` | Legacy compatibility mode (no authentication) |

> ⚠️ GCM mode is recommended for all new applications.

---

## 🧼 Example Output Format

Encrypted data is a **Base64-encoded JSON** like this:

```json
{
  "iv": "r7KfWkJcGlZcL7hYp6oJrQ==",
  "value": "J9PDpax7oMGJ6M4qYQ==",
  "cipher": "YWVzLTI1Ni1nY20=",
  "tag": "AQIDBAUGBwgJCgsMDQ=="
}
```

Entire JSON is Base64 encoded again to make it safe for database or URL storage.

---

## 🔧 Methods Summary

| Method | Description |
|--------|--------------|
| `__construct(?string $key = null)` | Initialize with custom or env key |
| `encrypt(string $data, ?string $cipher = null, string $add = '')` | Encrypt data |
| `decrypt(string $data, string $add = '')` | Decrypt data |
| `verify(string $plain, string $encrypted, string $add = '')` | Check if decrypted value matches plain text |

---

## 🧱 Example Integration (with Laravel)

```php
// config/app.php
'providers' => [
    Lazervel\Cryptor\Cryptor::class,
],

// usage
$cryptor = app(Lazervel\Cryptor\Cryptor::class);
$encrypted = $cryptor->encrypt('Secret Message');
```

---

## 🧾 License

This package is open-sourced software licensed under the **MIT License**.

---

## 💬 Author

**Afsara**  
Developer of [Lazervel](https://github.com/afsara) — a collection of modern PHP libraries for secure, modular development.

---
