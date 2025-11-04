<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($check->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $conn->query("INSERT INTO users (username, password) VALUES ('$username', '$password')");
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<h2>Register Akun</h2>
<form method="post">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Register</button>
</form>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<p>Sudah punya akun? <a href="index.php">Login</a></p>
</body>
</html>
