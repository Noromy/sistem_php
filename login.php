<?php
session_start();
require_once 'config.php';

// LOGIKA PEMERIKSAAN COOKIE (Auto-Login dari Cookie)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user']) && isset($_COOKIE['remember_token'])) {
    $cookie_user = $_COOKIE['remember_user'];
    $cookie_token = $_COOKIE['remember_token'];

    // Cari user berdasarkan username di database
    $query = "SELECT * FROM users WHERE username = $1";
    $result = pg_query_params($conn, $query, array($cookie_user));

    if (pg_num_rows($result) === 1) {
        $user = pg_fetch_assoc($result);

        // Verifikasi token cookie dengan token yang tersimpan di DB
        if (!empty($user['remember_token']) && hash_equals($user['remember_token'], hash('sha256', $cookie_token))) {
            // Set session otomatis
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        }
    }
}

// Redirect jika sudah memegang Session aktif
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// LOGIKA FORM SUBMIT LOGIN
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']); // Checkbox "Remember Me"

    if (empty($username) || empty($password)) {
        $error = "Username dan password tidak boleh kosong!";
    } else {
        // Cari pengguna berdasarkan username
        $query = "SELECT * FROM users WHERE username = $1";
        $result = pg_query_params($conn, $query, array($username));

        if (pg_num_rows($result) === 1) {
            $user = pg_fetch_assoc($result);

            // Verifikasi Password Hash
            if (password_verify($password, $user['password'])) {
                // 1. SET SESSION
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // 2. SET COOKIE (Jika opsi "Remember Me" dicentang)
                if ($remember) {
                    // Generate random token acak & aman
                    $random_token = bin2hex(random_bytes(32));
                    $hashed_token = hash('sha256', $random_token);

                    // Simpan token ke database PostgreSQL
                    $update_query = "UPDATE users SET remember_token = $1 WHERE id = $2";
                    pg_query_params($conn, $update_query, array($hashed_token, $user['id']));

                    // Simpan Cookie di browser pengguna (Berlaku 7 Hari)
                    setcookie('remember_user', $user['username'], time() + (86400 * 7), "/", "", false, true);
                    setcookie('remember_token', $random_token, time() + (86400 * 7), "/", "", false, true);
                }

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-container">
    <h2>Login Sistem</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Masukkan username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>
        </div>
        <div class="remember-me">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Ingat Saya (Cookie)</label>
        </div>
        <button type="submit" class="btn-submit">Masuk</button>
    </form>
    <div class="auth-footer">
        Belum punya akun? <a href="register.php">Daftar sekarang</a>
    </div>
</div>
</body>
</html>