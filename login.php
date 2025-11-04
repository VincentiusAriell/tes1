<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

if (is_logged_in()) {
	redirect('chats.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim((string)($_POST['username'] ?? ''));
	$password = (string)($_POST['password'] ?? '');

	if ($username === '' || $password === '') {
		$errors[] = 'Username and password are required.';
	} else {
		$pdo = db();
		$stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = ?');
		$stmt->execute([$username]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($row && password_verify($password, $row['password_hash'])) {
			$_SESSION['user_id'] = (int)$row['id'];
			$_SESSION['username'] = $username;
			redirect('chats.php');
		} else {
			$errors[] = 'Invalid credentials.';
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
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
		<a href="register.php">Register</a>
	</div>
	<h2>Login</h2>
	<?php if ($errors): ?>
		<div class="error">
			<?php foreach ($errors as $err): ?>
				<div><?= sanitize($err) ?></div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<form method="post" action="login.php">
		<label>
			Username
			<input type="text" name="username" required value="<?= isset($username) ? sanitize($username) : '' ?>">
		</label>
		<label>
			Password
			<input type="password" name="password" required>
		</label>
		<button type="submit">Login</button>
	</form>
</body>
</html>


