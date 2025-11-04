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
?>
