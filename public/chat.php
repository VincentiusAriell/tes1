<?php
include __DIR__ . '/../app/functions.php';
include __DIR__ . '/../config/config.php';
checkLogin();

$user_id = $_SESSION['user_id'];
$conversation_id = $_GET['conversation_id'] ?? 0;

// Ambil info percakapan
$stmt = $conn->prepare("
    SELECT 
        c.id,
        IF(c.user_one = ?, c.user_two, c.user_one) AS other_user_id,
        u.name AS other_user_name,
        u.profile_picture
    FROM conversations c
    JOIN users u ON u.id = IF(c.user_one = ?, c.user_two, c.user_one)
    WHERE c.id = ?
");
$stmt->bind_param("iii", $user_id, $user_id, $conversation_id);
$stmt->execute();
$chat = $stmt->get_result()->fetch_assoc();

if (!$chat) {
    die("Percakapan tidak ditemukan.");
}

$other_user_id = $chat['other_user_id'];

// Kirim pesan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = trim($_POST['message'] ?? '');
    $message_type = 'text';
    $file_path = null;

    // Upload file
    if (!empty($_FILES['file']['name'])) {
        $file_name = time() . "_" . basename($_FILES['file']['name']);
        $target_dir = __DIR__ . "/uploads/";
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            $file_path = "uploads/" . $file_name;
            if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                $message_type = 'image';
            } else {
                $message_type = 'file';
            }
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO messages (conversation_id, sender_id, receiver_id, message_type, message_text, file_path, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iiisss", $conversation_id, $user_id, $other_user_id, $message_type, $message, $file_path);
    $stmt->execute();

    header("Location: chat.php?conversation_id=" . $conversation_id);
    exit();
}

// Ambil semua pesan
$stmt = $conn->prepare("
    SELECT m.*, u.name, u.profile_picture
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.conversation_id = ?
    ORDER BY m.created_at ASC
");
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$messages = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat dengan <?= htmlspecialchars($chat['other_user_name']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; margin: 0; padding: 0; }
        .chat-header { background: #3498db; color: white; padding: 15px; display: flex; align-items: center; }
        .chat-header img { border-radius: 50%; margin-right: 10px; }
        .chat-box { padding: 15px; height: 400px; overflow-y: auto; background: white; border-bottom: 1px solid #ddd; }
        .msg { margin-bottom: 10px; padding: 8px; border-radius: 10px; max-width: 60%; word-wrap: break-word; }
        .msg.me { background: #d1f0ff; margin-left: auto; }
        .msg.other { background: #f1f1f1; }
        .file-preview { display: flex; align-items: center; margin-top: 5px; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 5px 8px; max-width: 250px; }
        .file-preview img.icon { width: 24px; height: 24px; margin-right: 8px; }
        form { display: flex; padding: 10px; background: #eee; align-items: center; }
        input[type="text"] { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        input[type="file"] { margin-left: 5px; }
        button { background: #3498db; border: none; color: white; padding: 10px 15px; margin-left: 5px; border-radius: 5px; cursor: pointer; }
        .back-btn { background: #e74c3c; color: white; text-decoration: none; padding: 8px 12px; border-radius: 5px; margin: 10px; display: inline-block; }
        img.chat-img { max-width: 150px; border-radius: 10px; display: block; margin-top: 5px; }
    </style>
</head>
<body>

<div class="chat-header">
    <img src="uploads/<?= $chat['profile_picture'] ? htmlspecialchars($chat['profile_picture']) : 'default.png'; ?>" width="40" height="40" alt="Foto Profil">
    <h3><?= htmlspecialchars($chat['other_user_name']); ?></h3>
</div>

<div class="chat-box" id="chat-box">
    <?php while ($msg = $messages->fetch_assoc()): ?>
        <div class="msg <?= $msg['sender_id'] == $user_id ? 'me' : 'other'; ?>">
            <strong><?= htmlspecialchars($msg['name']); ?>:</strong><br>

            <?php if ($msg['message_type'] == 'image' && $msg['file_path']): ?>
                <img src="<?= htmlspecialchars($msg['file_path']); ?>" class="chat-img" alt="Gambar">

            <?php elseif ($msg['message_type'] == 'file' && $msg['file_path']): ?>
                <?php 
                    $ext = strtolower(pathinfo($msg['file_path'], PATHINFO_EXTENSION));
                    $file_icon = 'assets/icons/file.png';
                    if ($ext === 'pdf') $file_icon = 'assets/icons/pdf.png';
                    elseif (in_array($ext, ['zip', 'rar'])) $file_icon = 'assets/icons/zip.png';
                    elseif (in_array($ext, ['doc', 'docx'])) $file_icon = 'assets/icons/doc.png';
                ?>
                <div class="file-preview">
                    <img src="<?= $file_icon ?>" class="icon" alt="file icon">
                    <a href="<?= htmlspecialchars($msg['file_path']); ?>" download><?= basename($msg['file_path']); ?></a>
                </div>
            <?php endif; ?>

            <?php if (!empty($msg['message_text'])): ?>
                <div><?= htmlspecialchars($msg['message_text']); ?></div>
            <?php endif; ?>

            <small style="font-size: 0.8em; color: gray;"><?= date('H:i', strtotime($msg['created_at'])); ?></small>
        </div>
    <?php endwhile; ?>
</div>

<form method="post" enctype="multipart/form-data">
    <input type="text" name="message" placeholder="Ketik pesan...">
    <input type="file" name="file" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.zip,.rar">
    <button type="submit">Kirim</button>
</form>

<a href="chatlist.php" class="back-btn">Kembali</a>

<script>
    const box = document.getElementById('chat-box');
    box.scrollTop = box.scrollHeight;
</script>

</body>
</html>
