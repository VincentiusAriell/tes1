<?php
session_start();
include __DIR__ . '/../config/config.php';
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("UPDATE users SET status = 'Offline', last_seen = NOW() WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

session_destroy();
header("Location: index.php");
exit();
?>
