<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim((string)($_POST['username'] ?? ''));
	$password = (string)($_POST['password'] ?? '');
	$confirm = (string)($_POST['confirm'] ?? '');

	if ($username === '' || $password === '') {
		$errors[] = 'Username and password are required.';
	}
	if ($password !== $confirm) {
		$errors[] = 'Password confirmation does not match.';
	}

	if (!$errors) {
		try {
			$pdo = db();
			$stmt = $pdo->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, ?)');
			$stmt->execute([
				$username,
				password_hash($password, PASSWORD_DEFAULT),
				date('c'),
			]);
			$userId = (int)$pdo->lastInsertId();
			$_SESSION['user_id'] = $userId;
			$_SESSION['username'] = $username;
			redirect('chats.php');
		} catch (PDOException $e) {
			if (str_contains(strtolower($e->getMessage()), 'unique')) {
				$errors[] = 'Username already taken.';
			} else {
				$errors[] = 'Registration failed.';
			}
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Register</title>
	<style>
		body { font-family: Arial, sans-serif; max-width: 480px; margin: 40px auto; padding: 0 16px; }
		form { display: grid; gap: 12px; }
		input[type=text], input[type=password] { padding: 10px; font-size: 16px; }
		button { padding: 10px 12px; font-size: 16px; cursor: pointer; }
		.error { background: #ffe5e5; color: #900; padding: 8px 10px; border: 1px solid #f5b5b5; }
		.topnav { margin-bottom: 16px; }
	</style>
</head>
<body>
	<div class="topnav">
		<a href="login.php">Login</a>
	</div>
	<h2>Create account</h2>
	<?php if ($errors): ?>
		<div class="error">
			<?php foreach ($errors as $err): ?>
				<div><?= sanitize($err) ?></div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<form method="post" action="register.php">
		<label>
			Username
			<input type="text" name="username" required value="<?= isset($username) ? sanitize($username) : '' ?>">
		</label>
		<label>
			Password
			<input type="password" name="password" required>
		</label>
		<label>
			Confirm Password
			<input type="password" name="confirm" required>
		</label>
		<button type="submit">Register</button>
	</form>
</body>
</html>


