<?php
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../app/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_plain = trim($_POST['password']);
    
    // Hash password dengan Whirlpool
    $password = hash('whirlpool', $password_plain);
    
    // Encrypt name with super encryption (AES-256-CBC + Camellia-256-CBC)
    $name_encrypted = superEncryptDB($name);
    

    // Cek apakah email sudah digunakan
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("Location: ../../public/register.php?error=used");
        exit();
    }

    // Simpan data user baru (name terenkripsi di database)
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $name_encrypted, $email, $password);

    if ($stmt->execute()) {
        header("Location: ../../public/index.php");
        exit();
    } else {
        header("Location: ../../public/register.php?error=fail");
        exit();
    }
}
?>
