<?php
declare(strict_types=1);

// Basic configuration and helpers
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

date_default_timezone_set('UTC');

$__DB = null;

function db(): PDO {
	global $__DB;
	if ($__DB instanceof PDO) {
		return $__DB;
	}
	$dir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}
	$dsn = 'sqlite:' . $dir . DIRECTORY_SEPARATOR . 'app.db';
	$pdo = new PDO($dsn);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->exec('PRAGMA foreign_keys = ON');
	$__DB = $pdo;
	return $pdo;
}

function current_user_id(): ?int {
	return isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
}

function current_username(): ?string {
	return isset($_SESSION['username']) ? strval($_SESSION['username']) : null;
}

function is_logged_in(): bool {
	return current_user_id() !== null;
}

function require_login(): void {
	if (!is_logged_in()) {
		header('Location: login.php');
		exit;
	}
}

function redirect(string $path): void {
	header('Location: ' . $path);
	exit;
}

function sanitize(string $value): string {
	return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

?>


