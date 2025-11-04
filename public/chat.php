<?php
include 'functions.php';
checkLogin();

$user_id = $_SESSION['user_id'];
$to_id = $_GET['to'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['message'];
    $conn->query("INSERT INTO messages (sender_id, receiver_id, message) VALUES ('$user_id', '$to_id', '$message')");
}

$chatUser = getUser($to_id);
$messages = $conn->query("SELECT * FROM messages WHERE 
    (sender_id='$user_id' AND receiver_id='$to_id') OR 
    (sender_id='$to_id' AND receiver_id='$user_id')
    ORDER BY created_at ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat dengan <?= $chatUser['username']; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<h3>Chat dengan <?= $chatUser['username']; ?></h3>

<div class="chat-box">
    <?php while($msg = $messages->fetch_assoc()): ?>
        <p><b><?= getUser($msg['sender_id'])['username']; ?>:</b> <?= htmlspecialchars($msg['message']); ?></p>
    <?php endwhile; ?>
</div>

<form method="post">
    <input type="text" name="message" placeholder="Ketik pesan..." required>
    <button type="submit">Kirim</button>
</form>

<a href="chatlist.php">Kembali</a>
</body>
</html>
