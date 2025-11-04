<?php
/**
 * Super Encryption Utility
 * Implements Caesar Cipher + RC4 + AES encryption/decryption
 */

// Caesar Cipher Encryption
function caesarEncrypt($text, $shift = 13) {
    $result = '';
    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    
    // Normalize shift to positive range
    $shift = $shift % 95;
    if ($shift < 0) {
        $shift += 95;
    }
    
    for ($i = 0; $i < mb_strlen($text, 'UTF-8'); $i++) {
        $char = mb_substr($text, $i, 1, 'UTF-8');
        $code = mb_ord($char, 'UTF-8');
        
        if ($code !== false) {
            // Apply Caesar cipher to printable ASCII (32-126)
            if ($code >= 32 && $code <= 126) {
                $newCode = (($code - 32 + $shift) % 95) + 32;
                $result .= mb_chr($newCode, 'UTF-8');
            } else {
                // For non-printable or extended characters, keep as-is
                // This preserves UTF-8 multibyte characters
                $result .= $char;
            }
        } else {
            $result .= $char;
        }
    }
    
    return $result;
}

// Caesar Cipher Decryption
function caesarDecrypt($text, $shift = 13) {
    return caesarEncrypt($text, -$shift);
}

// RC4 Key Scheduling Algorithm
function rc4Init($key) {
    $S = range(0, 255);
    $j = 0;
    $keyLength = strlen($key);
    
    for ($i = 0; $i < 256; $i++) {
        $j = ($j + $S[$i] + ord($key[$i % $keyLength])) % 256;
        // Swap
        $temp = $S[$i];
        $S[$i] = $S[$j];
        $S[$j] = $temp;
    }
    
    return $S;
}

// RC4 Encryption/Decryption (symmetric)
function rc4Crypt($data, $key) {
    $S = rc4Init($key);
    $i = 0;
    $j = 0;
    $result = '';
    $dataLength = strlen($data);
    
    for ($k = 0; $k < $dataLength; $k++) {
        $i = ($i + 1) % 256;
        $j = ($j + $S[$i]) % 256;
        // Swap
        $temp = $S[$i];
        $S[$i] = $S[$j];
        $S[$j] = $temp;
        // Generate keystream byte
        $K = $S[($S[$i] + $S[$j]) % 256];
        // XOR with data
        $result .= chr(ord($data[$k]) ^ $K);
    }
    
    return $result;
}

// AES Encryption (using OpenSSL)
function aesEncrypt($data, $key, $iv) {
    // Use AES-256-CBC
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    if ($encrypted === false) {
        throw new Exception('AES encryption failed');
    }
    return base64_encode($encrypted);
}

// AES Decryption (using OpenSSL)
function aesDecrypt($data, $key, $iv) {
    // Decode from base64
    $data = base64_decode($data);
    $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    if ($decrypted === false) {
        throw new Exception('AES decryption failed');
    }
    return $decrypted;
}

// Super Encryption: Caesar -> RC4 -> AES
function superEncrypt($plaintext, $caesarShift = 13, $rc4Key = null, $aesKey = null, $aesIV = null) {
    global $ENCRYPTION_CONFIG;
    
    // Use defaults from config if not provided
    if ($rc4Key === null) {
        $rc4Key = $ENCRYPTION_CONFIG['rc4_key'];
    }
    if ($aesKey === null) {
        $aesKey = $ENCRYPTION_CONFIG['aes_key'];
    }
    if ($aesIV === null) {
        $aesIV = $ENCRYPTION_CONFIG['aes_iv'];
    }
    
    // Step 1: Caesar Cipher Encryption
    $step1 = caesarEncrypt($plaintext, $caesarShift);
    
    // Step 2: RC4 Encryption
    $step2 = rc4Crypt($step1, $rc4Key);
    
    // Step 3: AES Encryption
    $step3 = aesEncrypt($step2, $aesKey, $aesIV);
    
    return $step3;
}

// Super Decryption: AES -> RC4 -> Caesar
function superDecrypt($ciphertext, $caesarShift = 13, $rc4Key = null, $aesKey = null, $aesIV = null) {
    global $ENCRYPTION_CONFIG;
    
    // Check if ciphertext is empty or null
    if (empty($ciphertext)) {
        return $ciphertext;
    }
    
    // Use defaults from config if not provided
    if ($rc4Key === null) {
        $rc4Key = $ENCRYPTION_CONFIG['rc4_key'];
    }
    if ($aesKey === null) {
        $aesKey = $ENCRYPTION_CONFIG['aes_key'];
    }
    if ($aesIV === null) {
        $aesIV = $ENCRYPTION_CONFIG['aes_iv'];
    }
    
    try {
        // Step 1: AES Decryption
        $step1 = aesDecrypt($ciphertext, $aesKey, $aesIV);
        
        // Step 2: RC4 Decryption
        $step2 = rc4Crypt($step1, $rc4Key);
        
        // Step 3: Caesar Cipher Decryption
        $step3 = caesarDecrypt($step2, $caesarShift);
        
        return $step3;
    } catch (Exception $e) {
        // If decryption fails, check if it's a base64 encoded string (AES encrypted)
        // If not, it might be an old unencrypted message, return as-is
        if (base64_encode(base64_decode($ciphertext, true)) === $ciphertext) {
            // It's base64 encoded but decryption failed - might be corrupted or wrong key
            // Return original to avoid breaking the app
            return $ciphertext;
        } else {
            // Not base64 - likely old unencrypted message
            return $ciphertext;
        }
    }
}

