<?php
require 'config.php';
session_start();

// hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// ambil input
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$captcha  = $_POST['g-recaptcha-response'] ?? '';

if (empty($captcha)) {
    $_SESSION['error'] = 'Tolong selesaikan captcha.';
    header('Location: login.php'); exit;
}

// verifikasi ke Google (gunakan cURL jika tersedia)
$secret = RECAPTCHA_SECRET_KEY;
$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

$postData = http_build_query([
    'secret' => $secret,
    'response' => $captcha,
    'remoteip' => $_SERVER['REMOTE_ADDR']
]);

$response = false;
// cURL lebih andal
if (function_exists('curl_version')) {
    $ch = curl_init($verifyUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
} else {
    // fallback jika tidak ada cURL
    $response = file_get_contents($verifyUrl . '?' . $postData);
}

if (!$response) {
    $_SESSION['error'] = 'Gagal memverifikasi captcha (tidak ada respon).';
    header('Location: login.php'); exit;
}

$result = json_decode($response, true);
if (empty($result['success'])) {
    // ambil error codes kalau ada
    $err = isset($result['error-codes']) ? implode(', ', $result['error-codes']) : 'unknown';
    $_SESSION['error'] = 'Captcha tidak valid. ' . $err;
    header('Location: login.php'); exit;
}

// (opsional) periksa hostname
if (isset($result['hostname']) && !in_array($result['hostname'], ['localhost', '127.0.0.1', $_SERVER['HTTP_HOST']])) {
    // bisa abaikan untuk localhost, tetapi aman untuk cek
}

// ---- setelah captcha valid: verifikasi username/password ----
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    $_SESSION['error'] = 'Gagal koneksi database.';
    header('Location: login.php'); exit;
}

// gunakan prepared statements untuk cegah SQL Injection
$stmt = $conn->prepare("SELECT id, password FROM admin WHERE username=?");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $hash);
    $stmt->fetch();
    // gunakan password_hash() / password_verify()
    if (password_verify($password, $hash) || md5($password) === $hash) {
        // login sukses
        $_SESSION['user_id'] = $id;
        header('Location: index.php'); exit;
    }
}

// jika sampai sini artinya gagal
$_SESSION['error'] = 'Username atau password salah.';
header('Location: login.php'); exit;
