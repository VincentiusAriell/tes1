<?php
session_start();
include __DIR__ . '/../config/config.php';



function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
}


function getUser($id) {
    global $conn;
    $result = $conn->query("SELECT * FROM users WHERE id='$id'");
    $user = $result->fetch_assoc();
    if ($user && isset($user['name'])) {
        // Decrypt name from database for display
        $user['name'] = superDecryptDB($user['name']);
    }
    return $user;
}

// Encryption helpers: Caesar -> RC4 -> AES (encrypt) and reverse for decrypt

function caesarEncrypt($text, $shift) {
    if ($text === null || $text === '') return $text;
    $shift = (int)$shift % 26;
    $output = '';
    $length = strlen($text);
    for ($i = 0; $i < $length; $i++) {
        $ch = $text[$i];
        $ord = ord($ch);
        if ($ord >= 65 && $ord <= 90) {
            $output .= chr((($ord - 65 + $shift) % 26) + 65);
        } elseif ($ord >= 97 && $ord <= 122) {
            $output .= chr((($ord - 97 + $shift) % 26) + 97);
        } else {
            $output .= $ch;
        }
    }
    return $output;
}

function caesarDecrypt($text, $shift) {
    return caesarEncrypt($text, 26 - ((int)$shift % 26));
}

// Super encryption functions (AES-256-CBC + Camellia-256-CBC)
function superEncryptDB($plaintext) {
    // Generate random keys and IVs
    $aes_key = openssl_random_pseudo_bytes(32); // 256 bits
    $camellia_key = openssl_random_pseudo_bytes(32); // 256 bits
    $aes_iv = openssl_random_pseudo_bytes(16);
    $camellia_iv = openssl_random_pseudo_bytes(16);
    
    // First layer: AES-256-CBC
    $aes_encrypted = openssl_encrypt($plaintext, 'aes-256-cbc', $aes_key, OPENSSL_RAW_DATA, $aes_iv);
    
    // Second layer: Camellia-256-CBC
    $camellia_encrypted = openssl_encrypt($aes_encrypted, 'camellia-256-cbc', $camellia_key, OPENSSL_RAW_DATA, $camellia_iv);
    
    // Combine all components for storage
    $combined = base64_encode($aes_key . $camellia_key . $aes_iv . $camellia_iv . $camellia_encrypted);
    
    return $combined;
}

function superDecryptDB($encrypted) {
    if (empty($encrypted)) return '';
    
    // Decode the combined string
    $decoded = base64_decode($encrypted);
    
    // Extract components
    $aes_key = substr($decoded, 0, 32);
    $camellia_key = substr($decoded, 32, 32);
    $aes_iv = substr($decoded, 64, 16);
    $camellia_iv = substr($decoded, 80, 16);
    $encrypted_data = substr($decoded, 96);
    
    // First layer: Decrypt Camellia
    $aes_encrypted = openssl_decrypt($encrypted_data, 'camellia-256-cbc', $camellia_key, OPENSSL_RAW_DATA, $camellia_iv);
    
    // Second layer: Decrypt AES
    $decrypted = openssl_decrypt($aes_encrypted, 'aes-256-cbc', $aes_key, OPENSSL_RAW_DATA, $aes_iv);
    
    return $decrypted;
}

function rc4Stream($key, $data) {
    if (empty($key)) $key = 'defaultkey';
    $s = range(0, 255);
    $j = 0;
    $keyLength = strlen($key);
    for ($i = 0; $i < 256; $i++) {
        $j = ($j + $s[$i] + ord($key[$i % $keyLength])) % 256;
        $tmp = $s[$i];
        $s[$i] = $s[$j];
        $s[$j] = $tmp;
    }
    $i = 0; $j = 0; $result = '';
    $dataLength = strlen($data);
    for ($y = 0; $y < $dataLength; $y++) {
        $i = ($i + 1) % 256;
        $j = ($j + $s[$i]) % 256;
        $tmp = $s[$i];
        $s[$i] = $s[$j];
        $s[$j] = $tmp;
        $k = $s[($s[$i] + $s[$j]) % 256];
        $result .= chr(ord($data[$y]) ^ $k);
    }
    return $result;
}

function aesEncrypt($plaintext, $key) {
    if ($plaintext === null || $plaintext === '') return ['cipher' => '', 'iv' => ''];
    $method = 'AES-256-CBC';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    $ciphertext = openssl_encrypt($plaintext, $method, hash('sha256', $key, true), OPENSSL_RAW_DATA, $iv);
    return [
        'cipher' => base64_encode($ciphertext),
        'iv' => base64_encode($iv)
    ];
}

function aesDecrypt($cipherB64, $ivB64, $key) {
    if (!$cipherB64) return '';
    $method = 'AES-256-CBC';
    $ciphertext = base64_decode($cipherB64);
    $iv = base64_decode($ivB64);
    $plaintext = openssl_decrypt($ciphertext, $method, hash('sha256', $key, true), OPENSSL_RAW_DATA, $iv);
    return $plaintext === false ? '' : $plaintext;
}


function superEncrypt($plainText) {
    global $APP_AES, $APP_RC4_KEY, $APP_CAESAR_SHIFT;
    if ($plainText === null || $plainText === '') return ['text' => $plainText, 'meta' => null];
    $step1 = caesarEncrypt($plainText, $APP_CAESAR_SHIFT);
    $step2 = rc4Stream($APP_RC4_KEY, $step1);
    $aes = aesEncrypt($step2, $APP_AES);
    $meta = json_encode(['iv' => $aes['iv'], 'alg' => 'caesar+rc4+aes256cbc']);
    return ['text' => $aes['cipher'], 'meta' => $meta];
}

function superDecrypt($cipherText, $metaJson) {
    global $APP_AES, $APP_RC4_KEY, $APP_CAESAR_SHIFT;
    if ($cipherText === null || $cipherText === '' || !$metaJson) return $cipherText;
    $meta = json_decode($metaJson, true);
    if (!is_array($meta) || empty($meta['iv'])) return $cipherText;
    $stepA = aesDecrypt($cipherText, $meta['iv'], $APP_AES);
    $stepB = rc4Stream($APP_RC4_KEY, $stepA);
    $plain = caesarDecrypt($stepB, $APP_CAESAR_SHIFT);
    return $plain;
}

function encryptFileCamellia($inputPath, $outputPath) {
    global $APP_AES; // gunakan kunci rahasia dari config
    if (!file_exists($inputPath)) return false;

    $method = 'camellia-256-cbc';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    $key = hash('sha256', $APP_AES, true);

    $data = file_get_contents($inputPath);
    if ($data === false) return false;

    $encrypted = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
    if ($encrypted === false) return false;

    // Gabungkan IV + data terenkripsi
    file_put_contents($outputPath, $iv . $encrypted);

    return true;
}

function decryptFileCamellia($inputPath, $outputPath) {
    global $APP_AES;
    if (!file_exists($inputPath)) return false;

    $method = 'camellia-256-cbc';
    $key = hash('sha256', $APP_AES, true);
    $ivLength = openssl_cipher_iv_length($method);

    $data = file_get_contents($inputPath);
    if ($data === false || strlen($data) <= $ivLength) return false;

    $iv = substr($data, 0, $ivLength);
    $encryptedData = substr($data, $ivLength);

    $decrypted = openssl_decrypt($encryptedData, $method, $key, OPENSSL_RAW_DATA, $iv);
    if ($decrypted === false) return false;

    file_put_contents($outputPath, $decrypted);
    return true;
}

function lsbEmbed($imagePath, $message) {
    if (!file_exists($imagePath)) return false;
    $img = imagecreatefromstring(file_get_contents($imagePath));
    if (!$img) return false;

    $message .= "\0"; // penanda akhir pesan
    $msgBin = '';
    for ($i = 0; $i < strlen($message); $i++) {
        $msgBin .= str_pad(decbin(ord($message[$i])), 8, '0', STR_PAD_LEFT);
    }

    $width = imagesx($img);
    $height = imagesy($img);
    $msgIndex = 0;
    $msgLength = strlen($msgBin);

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            if ($msgIndex >= $msgLength) break 2;
            $rgb = imagecolorat($img, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            $b = ($b & 0xFE) | (int)$msgBin[$msgIndex];
            $msgIndex++;

            $color = imagecolorallocate($img, $r, $g, $b);
            imagesetpixel($img, $x, $y, $color);
        }
    }

    imagepng($img, $imagePath); // simpan ulang gambar
    imagedestroy($img);
    return true;
}

function lsbExtract($imagePath) {
    if (!file_exists($imagePath)) return '';
    $img = imagecreatefromstring(file_get_contents($imagePath));
    if (!$img) return '';

    $width = imagesx($img);
    $height = imagesy($img);
    $binData = '';

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($img, $x, $y);
            $b = $rgb & 0xFF;
            $binData .= ($b & 1);
        }
    }

    $chars = '';
    for ($i = 0; $i < strlen($binData); $i += 8) {
        $byte = substr($binData, $i, 8);
        $char = chr(bindec($byte));
        if ($char === "\0") break;
        $chars .= $char;
    }

    imagedestroy($img);
    return $chars;
}



?>