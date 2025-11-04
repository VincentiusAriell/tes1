<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require_login();

$pdo = db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = (string)($_POST['action'] ?? '');
	if ($action === 'create') {
		$title = trim((string)($_POST['title'] ?? ''));
		if ($title === '') {
			$errors[] = 'Chat title is required.';
		} else {
			$pdo->beginTransaction();
			try {
				$stmt = $pdo->prepare('INSERT INTO chats (title, created_by, created_at) VALUES (?, ?, ?)');
				$stmt->execute([$title, current_user_id(), date('c')]);
				$chatId = (int)$pdo->lastInsertId();
				$pdo->prepare('INSERT INTO chat_members (chat_id, user_id, joined_at) VALUES (?, ?, ?)')
					->execute([$chatId, current_user_id(), date('c')]);
				$pdo->commit();
				redirect('chat.php?id=' . $chatId);
			} catch (Throwable $e) {
				$pdo->rollBack();
				$errors[] = 'Failed to create chat.';
			}
		}
	}
	if ($action === 'join') {
		$chatId = (int)($_POST['chat_id'] ?? 0);
		if ($chatId > 0) {
			try {
				$pdo->prepare('INSERT OR IGNORE INTO chat_members (chat_id, user_id, joined_at) VALUES (?, ?, ?)')
					->execute([$chatId, current_user_id(), date('c')]);
				redirect('chat.php?id=' . $chatId);
			} catch (Throwable $e) {
				$errors[] = 'Failed to join chat.';
			}
		}
	}
}

// User's chats
$myChatsStmt = $pdo->prepare(
	'SELECT c.id, c.title, c.created_at
	 FROM chats c
	 JOIN chat_members m ON m.chat_id = c.id
	 WHERE m.user_id = ?
	 ORDER BY c.created_at DESC'
);
$myChatsStmt->execute([current_user_id()]);
$myChats = $myChatsStmt->fetchAll(PDO::FETCH_ASSOC);

// Other chats (not a member)
$otherChatsStmt = $pdo->prepare(
	'SELECT c.id, c.title
	 FROM chats c
	 WHERE c.id NOT IN (
	 	SELECT chat_id FROM chat_members WHERE user_id = ?
	 )
	 ORDER BY c.created_at DESC LIMIT 20'
);
$otherChatsStmt->execute([current_user_id()]);
$otherChats = $otherChatsStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Chats</title>
	<style>
		body { font-family: Arial, sans-serif; max-width: 860px; margin: 24px auto; padding: 0 16px; }
		a { text-decoration: none; }
		.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
		.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
		.card { border: 1px solid #ddd; padding: 12px; border-radius: 6px; }
		ul { list-style: none; padding: 0; margin: 0; }
		li { padding: 8px 0; border-bottom: 1px solid #eee; }
		.error { background: #ffe5e5; color: #900; padding: 8px 10px; border: 1px solid #f5b5b5; margin-bottom: 12px; }
		input[type=text] { padding: 8px; font-size: 16px; width: 100%; }
		button { padding: 8px 10px; font-size: 14px; cursor: pointer; }
	</style>
</head>
<body>
	<div class="header">
		<div>
			<strong>Welcome, <?= sanitize((string)current_username()) ?></strong>
		</div>
		<div>
			<a href="logout.php">Logout</a>
		</div>
	</div>

	<?php if ($errors): ?>
		<div class="error">
			<?php foreach ($errors as $e): ?>
				<div><?= sanitize($e) ?></div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="grid">
		<div class="card">
			<h3>Your chats</h3>
			<ul>
				<?php if (!$myChats): ?>
					<li>No chats yet.</li>
				<?php endif; ?>
				<?php foreach ($myChats as $c): ?>
					<li>
						<a href="chat.php?id=<?= (int)$c['id'] ?>"><?= sanitize((string)$c['title']) ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="card">
			<h3>Create chat</h3>
			<form method="post">
				<input type="hidden" name="action" value="create">
				<input type="text" name="title" placeholder="Chat title" required>
				<div style="margin-top:8px">
					<button type="submit">Create</button>
				</div>
			</form>
			<h3 style="margin-top:24px">Discover</h3>
			<ul>
				<?php if (!$otherChats): ?>
					<li>No chats to join.</li>
				<?php endif; ?>
				<?php foreach ($otherChats as $c): ?>
					<li style="display:flex; justify-content: space-between; align-items:center; gap: 12px;">
						<span><?= sanitize((string)$c['title']) ?></span>
						<form method="post" style="margin:0">
							<input type="hidden" name="action" value="join">
							<input type="hidden" name="chat_id" value="<?= (int)$c['id'] ?>">
							<button type="submit">Join</button>
						</form>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</body>
</html>


