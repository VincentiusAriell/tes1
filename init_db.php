<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

$pdo = db();

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS users (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		username TEXT NOT NULL UNIQUE,
		password_hash TEXT NOT NULL,
		created_at TEXT NOT NULL
	)'
);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS chats (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		title TEXT NOT NULL,
		created_by INTEGER NOT NULL,
		created_at TEXT NOT NULL,
		FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE CASCADE
	)'
);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS chat_members (
		chat_id INTEGER NOT NULL,
		user_id INTEGER NOT NULL,
		joined_at TEXT NOT NULL,
		PRIMARY KEY (chat_id, user_id),
		FOREIGN KEY(chat_id) REFERENCES chats(id) ON DELETE CASCADE,
		FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
	)'
);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS messages (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		chat_id INTEGER NOT NULL,
		sender_id INTEGER NOT NULL,
		content TEXT NOT NULL,
		created_at TEXT NOT NULL,
		FOREIGN KEY(chat_id) REFERENCES chats(id) ON DELETE CASCADE,
		FOREIGN KEY(sender_id) REFERENCES users(id) ON DELETE CASCADE
	)'
);

echo "Database initialized successfully.\n";

?>


