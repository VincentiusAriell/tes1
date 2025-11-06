<?php
include __DIR__ . '/../app/functions.php';
include __DIR__ . '/../config/config.php';
checkLogin();

$user_id = $_SESSION['user_id'];

// Jika user mengirim form email untuk mulai chat
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Cek apakah email ada di database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($other_user_id);
        $stmt->fetch();

        // Cek apakah percakapan sudah ada
        $check = $conn->prepare("
            SELECT id FROM conversations 
            WHERE (user_one = ? AND user_two = ?) OR (user_one = ? AND user_two = ?)
        ");
        $check->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $check->bind_result($conversation_id);
            $check->fetch();
        } else {
            // Buat percakapan baru
            $insert = $conn->prepare("INSERT INTO conversations (user_one, user_two) VALUES (?, ?)");
            $insert->bind_param("ii", $user_id, $other_user_id);
            $insert->execute();
            $conversation_id = $insert->insert_id;
        }

        // Redirect ke halaman chat
        header("Location: chat.php?conversation_id=" . $conversation_id);
        exit();
    } else {
        $error_message = "Email tidak ditemukan.";
    }
}

$sql = "
    SELECT c.id AS conversation_id,
           IF(c.user_one = ?, c.user_two, c.user_one) AS other_user_id,
           u.name AS other_user_name,
           u.profile_picture,
           m.message_text,
           m.message_type,
           m.created_at
    FROM conversations c
    JOIN users u ON u.id = IF(c.user_one = ?, c.user_two, c.user_one)
    LEFT JOIN messages m ON m.id = (
        SELECT id FROM messages 
        WHERE conversation_id = c.id 
        ORDER BY created_at DESC LIMIT 1
    )
    WHERE c.user_one = ? OR c.user_two = ?
    ORDER BY m.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Chat</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .header a {
            margin-left: 10px;
            text-decoration: none;
            background-color: #3498db;
            color: white;
            padding: 6px 10px;
            border-radius: 5px;
        }
        .header a.logout {
            background-color: #e74c3c;
        }
        .chat-item {
            display: flex;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
        }
        .chat-item:hover {
            background-color: #f5f5f5;
        }
        .chat-item img {
            border-radius: 50%;
            margin-right: 10px;
        }
        .chat-info {
            flex: 1;
        }
        .time {
            font-size: 0.85em;
            color: gray;
        }
        .new-chat-btn {
            display: inline-block;
            background-color: #2ecc71;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            margin-bottom: 10px;
        }
        .popup {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
        }
        .popup-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 300px;
        }
        .popup-content input[type="email"] {
            width: 90%;
            padding: 8px;
            margin-bottom: 10px;
        }
        .popup-content button {
            padding: 6px 10px;
            border: none;
            border-radius: 5px;
            background-color: #3498db;
            color: white;
            cursor: pointer;
        }
        .popup-content .cancel {
            background-color: #e74c3c;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="header">
    <h2>Daftar Chat</h2>
    <div>
        <span>Halo, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
        <a href="edit_profile.php">Edit Profil</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>

<a href="#" class="new-chat-btn" onclick="openPopup()">Mulai Chat</a>

<?php if (!empty($error_message)): ?>
    <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="chat-list">
<?php 
$rows = [];
while ($row = $result->fetch_assoc()) {
    // Dekripsi name untuk setiap user
    if (isset($row['other_user_name'])) {
        $row['other_user_name'] = superDecryptDB($row['other_user_name']);
    }
    $rows[] = $row;
}
if (count($rows) > 0): ?>
    <?php foreach ($rows as $row): ?>
        <div class="chat-item" onclick="window.location.href='chat.php?conversation_id=<?php echo $row['conversation_id']; ?>'">
            <img src="uploads/<?php echo $row['profile_picture'] ? htmlspecialchars($row['profile_picture']) : 'default.png'; ?>" 
                 alt="Foto Profil" width="50" height="50">
            <div class="chat-info">
                <strong><?php echo htmlspecialchars($row['other_user_name']); ?></strong><br>
                <?php if ($row['message_type'] == 'image'): ?>
                    <span>[Gambar]</span>
                <?php elseif ($row['message_type'] == 'file'): ?>
                    <span>[File]</span>
                <?php else: ?>
                    <span><?php echo htmlspecialchars(mb_strimwidth($row['message_text'], 0, 40, '...')); ?></span>
                <?php endif; ?>
                <div class="time"><?php echo date('d M H:i', strtotime($row['created_at'])); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Tidak ada percakapan.</p>
<?php endif; ?>
</div>

<div class="popup" id="popupForm">
    <div class="popup-content">
        <h3>Mulai Chat Baru</h3>
        <form method="POST">
            <input type="email" name="email" placeholder="Masukkan email pengguna" required>
            <br>
            <button type="submit">Mulai</button>
            <button type="button" class="cancel" onclick="closePopup()">Batal</button>
        </form>
    </div>
</div>

<script>
function openPopup() {
    document.getElementById("popupForm").style.display = "flex";
}
function closePopup() {
    document.getElementById("popupForm").style.display = "none";
}
</script>
</body>
</html>
