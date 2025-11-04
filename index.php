<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // First try default PHP password hashing verification (e.g., bcrypt)
        $isValid = password_verify($password, $row['password']);

        // If that fails, fall back to Whirlpool hash comparison (expects DB to store Whirlpool hex)
        if (!$isValid) {
            $whirlpoolHash = hash('whirlpool', $password);
            $isValid = hash_equals($row['password'], $whirlpoolHash);
        }

        if ($isValid) {
            $_SESSION['user_id'] = $row['id'];
            header("Location: chatlist.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "User tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<h2>Login</h2>
<form method="post">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
</form>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</body>
</html>
