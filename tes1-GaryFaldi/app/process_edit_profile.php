<?php
session_start();
include __DIR__ . '/../config/config.php';
include __DIR__ . '/../app/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data lama
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$current_picture = $user['profile_picture'];

// Jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $status = trim($_POST['status']);
    $profile_picture = $current_picture;

    // Upload foto baru jika ada
    if (!empty($_FILES['profile_picture']['name'])) {
        $file_name = time() . "_" . basename($_FILES['profile_picture']['name']);
        $target_dir = __DIR__ . "/../public/uploads/";
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array($file_type, $allowed)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                $profile_picture = $file_name;
            }
        }
    }

    // Enkripsi name sebelum disimpan ke database
    $name_encrypted = superEncryptDB($name);
    
    // Update ke database (name terenkripsi di database)
    $update = $conn->prepare("UPDATE users SET name = ?, status = ?, profile_picture = ? WHERE id = ?");
    $update->bind_param("sssi", $name_encrypted, $status, $profile_picture, $user_id);

    if ($update->execute()) {
        $_SESSION['name'] = $name; // Simpan nama asli (belum terenkripsi) di session
        header("Location: ../public/chats.php?updated=1");
        exit();
    } else {
        header("Location: ../public/edit_profile.php?error=updatefail");
        exit();
    }
}
?>
