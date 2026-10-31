<?php
session_start();
require_once 'config.php';

// Jika pengguna sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Memproses input saat method POST diterima
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitasi input pengguna
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Semua kolom harus diisi!";
    } else {
        // Cek apakah username sudah terdaftar menggunakan Prepared Statement
        $query = "SELECT id FROM users WHERE username = $1";
        $result = pg_query_params($conn, $query, array($username));

        if (pg_num_rows($result) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Enkripsi password menggunakan BCRYPT
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Simpan akun baru ke database PostgreSQL
            $insert_query = "INSERT INTO users (username, password) VALUES ($1, $2)";
            $insert_result = pg_query_params($conn, $insert_query, array($username, $hashed_password));

            if ($insert_result) {
                $success = "Registrasi berhasil! Silakan <a href='login.php'>login</a>.";
            } else {
                $error = "Gagal mendaftar, coba lagi nanti.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-container">
    <h2>Daftar Akun</h2>

    <!-- Tampilkan Notifikasi Error/Sukses -->
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Masukkan username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>
        </div>
        <button type="submit" class="btn-submit">Daftar</button>
    </form>
    <div class="auth-footer">
        Sudah punya akun? <a href="login.php">Login di sini</a>
    </div>
</div>
</body>
</html>