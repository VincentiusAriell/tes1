<?php
include 'config.php';
include 'encryption.php';
include 'function.php';
checkLogin();

$user_id = $_SESSION['user_id'];
$to_id = $_GET['to'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['message'];
    
    // Encrypt message using super encryption (Caesar + RC4 + AES)
    $encryptedMessage = superEncrypt($message);
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $to_id, $encryptedMessage);
    $stmt->execute();
    $stmt->close();
}

$chatUser = getUser($to_id);

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM messages WHERE 
    (sender_id=? AND receiver_id=?) OR 
    (sender_id=? AND receiver_id=?)
    ORDER BY created_at ASC");
$stmt->bind_param("iiii", $user_id, $to_id, $to_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result();
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
        <?php 
        // Decrypt message using super decryption (AES -> RC4 -> Caesar)
        $decryptedMessage = superDecrypt($msg['message']);
        ?>
        <p><b><?= getUser($msg['sender_id'])['username']; ?>:</b> <?= htmlspecialchars($decryptedMessage); ?></p>
    <?php endwhile; ?>
</div>

<form method="post">
    <input type="text" name="message" placeholder="Ketik pesan..." required>
    <button type="submit">Kirim</button>
</form>

<a href="chatlist.php">Kembali</a>
</body>
</html>
