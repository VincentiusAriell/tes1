<?php
session_start();
include __DIR__ . '/../../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $isValid = password_verify($password, $row['password']);

        if (!$isValid) {
            $whirlpoolHash = hash('whirlpool', $password);
            $isValid = hash_equals($row['password'], $whirlpoolHash);
        }

        if ($isValid) {
            $update = $conn->prepare("UPDATE users SET last_seen = NOW(), status = 'Online' WHERE id = ?");
            $update->bind_param("i", $row['id']);
            $update->execute();

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['email'] = $row['email'];

            header("Location: ../../public/chats.php");
            exit();
        } else {
            header("Location: ../../public/index.php?error=wrongpass");
            exit();
        }
    } else {
        header("Location: ../../public/index.php?error=notfound");
        exit();
    }
}
