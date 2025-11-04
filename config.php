<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "chat_web";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Encryption Configuration
// Caesar Cipher shift (default: 13)
define('CAESAR_SHIFT', 13);

// RC4 Key (should be at least 8 characters for security)
define('RC4_KEY', 'ChatAppSecureKey2024!RC4');

// AES Configuration (AES-256-CBC requires 32-byte key and 16-byte IV)
// Generate these once and keep them constant for all messages
define('AES_KEY', hash('sha256', 'ChatAppAESKey2024!SecureKeyForEncryption', true)); // 32 bytes
define('AES_IV', substr(hash('sha256', 'ChatAppIV2024!InitializationVector', true), 0, 16)); // 16 bytes

// Encryption config array for easy access
$ENCRYPTION_CONFIG = [
    'caesar_shift' => CAESAR_SHIFT,
    'rc4_key' => RC4_KEY,
    'aes_key' => AES_KEY,
    'aes_iv' => AES_IV
];
?>
