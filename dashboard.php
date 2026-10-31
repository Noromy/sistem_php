<?php
session_start();

// Proteksi Halaman: Cek apakah session user_id sudah ada
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="dashboard-card">
    <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p style="margin-top: 15px; color: #666;">
        Anda berhasil masuk menggunakan sistem Autentikasi PHP & PostgreSQL dengan Session.
    </p>
    <a href="logout.php" class="btn-logout">Logout / Keluar</a>
</div>
</body>
</html>