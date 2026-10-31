<?php
session_start();
require_once 'config.php';

// Hapus remember_token di DB jika user sedang login
if (isset($_SESSION['user_id'])) {
    $update_query = "UPDATE users SET remember_token = NULL WHERE id = $1";
    pg_query_params($conn, $update_query, array($_SESSION['user_id']));
}

// 1. HAPUS SEMUA DATA SESSION
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// 2. HAPUS COOKIE "REMEMBER ME"
setcookie('remember_user', '', time() - 3600, "/");
setcookie('remember_token', '', time() - 3600, "/");

// Redirect kembali ke halaman login
header("Location: login.php");
exit();
?>