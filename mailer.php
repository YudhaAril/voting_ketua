<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendOTP($to, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'YOUR_GMAIL@gmail.com'; // ganti
        $mail->Password   = 'YOUR_APP_PASSWORD';    // pakai App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('YOUR_GMAIL@gmail.com', 'Voting App');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = 'Kode OTP Verifikasi Akun';
        $mail->Body    = "Halo, kode OTP untuk verifikasi akun Anda adalah: <b>$otp</b>";

        $mail->send();
    } catch (Exception $e) {
        echo "OTP gagal dikirim. Error: {$mail->ErrorInfo}";
    }
}
