<?php
include 'functions.php';
checkLogin();

$user_id = $_SESSION['user_id'];
$users = $conn->query("SELECT * FROM users WHERE id != '$user_id'");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Chat</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<h2>Selamat datang, <?= getUser($user_id)['username']; ?>!</h2>
<h3>Daftar Pengguna</h3>
<ul>
    <?php while($u = $users->fetch_assoc()): ?>
        <li><a href="chat.php?to=<?= $u['id'] ?>"><?= $u['username'] ?></a></li>
    <?php endwhile; ?>
</ul>
<a href="logout.php">Logout</a>
</body>
</html>
