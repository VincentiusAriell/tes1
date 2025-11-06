<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "kripton3";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$APP_AES = getenv('APP_AES') ?: 'Zf9mL0sT4nV2xP8wQ3bR7kE6yJ1dC5';
$APP_RC4_KEY = getenv('APP_RC4_KEY') ?: 'rc4key-9F7hD3pQwT8rX2vZ';
$APP_CAESAR_SHIFT = (int)(getenv('APP_CAESAR_SHIFT') ?: 7);
$APP_TWOFISH_KEY = getenv('APP_TWOFISH_KEY') ?: 'twofishK3y_M9rT7pQ1bL5nD8';

?>