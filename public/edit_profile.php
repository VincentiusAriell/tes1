<?php
session_start();
include __DIR__ . '/../config/config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$stmt = $conn->prepare("SELECT name, email, profile_picture, status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profil</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: Arial;
            max-width: 500px;
            margin: 30px auto;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
        h2 { text-align: center; }
        form { display: flex; flex-direction: column; gap: 12px; }
        input, textarea {
            padding: 10px; border: 1px solid #ccc; border-radius: 6px;
        }
        button {
            background-color: #3498db; color: white;
            border: none; padding: 10px; border-radius: 6px; cursor: pointer;
        }
        button:hover { background-color: #2980b9; }
        .back {
            display: inline-block; margin-bottom: 15px; color: #555; text-decoration: none;
        }
        .back:hover { text-decoration: underline; }
        .profile-preview { text-align: center; }
        .profile-preview img {
            border-radius: 50%; width: 100px; height: 100px; object-fit: cover;
        }
    </style>
</head>
<body>
<a href="chats.php" class="back">← Kembali ke Chat</a>
<h2>Edit Profil</h2>

<div class="profile-preview">
    <img src="uploads/<?php echo $user['profile_picture'] ? htmlspecialchars($user['profile_picture']) : 'default.png'; ?>" alt="Foto Profil">
</div>

<form method="post" enctype="multipart/form-data" action="../app/process_edit_profile.php">
    <label>Nama Lengkap:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

    <label>Email:</label>
    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>

    <label>Status:</label>
    <input type="text" name="status" value="<?php echo htmlspecialchars($user['status']); ?>">

    <label>Foto Profil:</label>
    <input type="file" name="profile_picture" accept=".jpg,.jpeg,.png">

    <button type="submit">Simpan Perubahan</button>
</form>

<?php if (isset($_GET['error'])) echo "<p style='color:red;'>Gagal memperbarui profil!</p>"; ?>
</body>
</html>
