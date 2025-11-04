<?php
include __DIR__ . '/../app/functions.php';
include __DIR__ . '/../config/config.php';
checkLogin();

$user_id = $_SESSION['user_id'];
$users = $conn->query("SELECT * FROM users WHERE id != '$user_id'");
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
</head>
<body>
<h2>Daftar Chat</h2>
<p>Halo, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>

<div class="chat-list">
<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
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
    <?php endwhile; ?>
<?php else: ?>
    <p>Tidak ada percakapan.</p>
<?php endif; ?>
</div>

<p><a href="logout.php">Logout</a></p>
</body>
</html>

