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
    return $result->fetch_assoc();
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

function rc4Stream($key, $data) {
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

function twofishEncrypt($plainText, $key) {
    if ($plainText === null || $plainText === '') return $plainText;
    $cipher = openssl_encrypt($plainText, 'BF-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
    return base64_encode($cipher);
}

function twofishDecrypt($cipherB64, $key) {
    if (!$cipherB64) return '';
    $cipher = base64_decode($cipherB64);
    $plainText = openssl_decrypt($cipher, 'BF-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
    return $plainText === false ? '' : $plainText;
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
?>
