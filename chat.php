<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require_login();

$pdo = db();
$chatId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($chatId <= 0) {
	redirect('chats.php');
}

// Ensure membership
$m = $pdo->prepare('SELECT 1 FROM chat_members WHERE chat_id = ? AND user_id = ?');
$m->execute([$chatId, current_user_id()]);
if (!$m->fetchColumn()) {
	redirect('chats.php');
}

// Fetch chat
$cstmt = $pdo->prepare('SELECT id, title FROM chats WHERE id = ?');
$cstmt->execute([$chatId]);
$chat = $cstmt->fetch(PDO::FETCH_ASSOC);
if (!$chat) {
	redirect('chats.php');
}

// Handle new message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$content = trim((string)($_POST['content'] ?? ''));
	if ($content !== '') {
		$pdo->prepare('INSERT INTO messages (chat_id, sender_id, content, created_at) VALUES (?, ?, ?, ?)')
			->execute([$chatId, current_user_id(), $content, date('c')]);
	}
	redirect('chat.php?id=' . $chatId);
}

// Fetch messages
$msgs = $pdo->prepare(
	'SELECT m.id, m.content, m.created_at, u.username as sender
	 FROM messages m
	 JOIN users u ON u.id = m.sender_id
	 WHERE m.chat_id = ?
	 ORDER BY m.created_at ASC, m.id ASC'
);
$msgs->execute([$chatId]);
$messages = $msgs->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= sanitize((string)$chat['title']) ?> - Chat</title>
	<meta http-equiv="refresh" content="30">
	<style>
		body { font-family: Arial, sans-serif; max-width: 860px; margin: 24px auto; padding: 0 16px; }
		.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
		.box { border: 1px solid #ddd; border-radius: 6px; padding: 12px; }
		.messages { height: 60vh; overflow-y: auto; display: grid; gap: 10px; }
		.msg { padding: 8px 10px; border: 1px solid #eee; border-radius: 6px; }
		.msg .meta { color: #666; font-size: 12px; margin-bottom: 4px; }
		form { display: grid; grid-template-columns: 1fr auto; gap: 8px; margin-top: 12px; }
		input[type=text] { padding: 10px; font-size: 16px; }
		button { padding: 10px 12px; font-size: 16px; cursor: pointer; }
		a { text-decoration: none; }
	</style>
</head>
<body>
	<div class="header">
		<div>
			<a href="chats.php">← Back</a>
		</div>
		<div>
			<strong><?= sanitize((string)$chat['title']) ?></strong>
		</div>
		<div>
			<a href="logout.php">Logout</a>
		</div>
	</div>

	<div class="box">
		<div class="messages" id="messages">
			<?php foreach ($messages as $m): ?>
				<div class="msg">
					<div class="meta">
						<strong><?= sanitize((string)$m['sender']) ?></strong>
						<span>• <?= sanitize(date('Y-m-d H:i', strtotime((string)$m['created_at']))) ?></span>
					</div>
					<div><?= nl2br(sanitize((string)$m['content'])) ?></div>
				</div>
			<?php endforeach; ?>
		</div>
		<form method="post" autocomplete="off">
			<input type="text" name="content" placeholder="Type a message" required>
			<button type="submit">Send</button>
		</form>
	</div>

	<script>
		const box = document.getElementById('messages');
		if (box) { box.scrollTop = box.scrollHeight; }
	</script>
</body>
</html>


