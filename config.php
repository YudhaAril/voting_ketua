<?php
// Konfigurasi reCAPTCHA (ganti dengan key Anda)
define('RECAPTCHA_SITE_KEY', '6Lc4x6YrAAAAAGe2SeeOGneFKPpGXKItaow9XEXk');
define('RECAPTCHA_SECRET_KEY', '6Lc4x6YrAAAAAMA7gp_a-_if8PP1cEsFWDr_iOcC');

// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'voting_ketua');

// Konfigurasi email (untuk pengiriman OTP)
define('EMAIL_FROM', 'your_email@gmail.com');
define('EMAIL_NAME', 'Voting System');

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>