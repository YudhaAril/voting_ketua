<?php
session_start();
require 'koneksi.php';

// Define reCAPTCHA keys
define('RECAPTCHA_SITE_KEY', '6Lc4x6YrAAAAAGe2SeeOGneFKPpGXKItaow9XEXk');
define('RECAPTCHA_SECRET_KEY', '6Lc4x6YrAAAAAMA7gp_a-_if8PP1cEsFWDr_iOcC');

$error = '';

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $recaptcha_response = $_POST['g-recaptcha-response'];

    // Validasi reCAPTCHA
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".RECAPTCHA_SECRET_KEY."&response=".$recaptcha_response);
    $captcha_success = json_decode($verify);

    if(!$captcha_success->success){
        $error = "Verifikasi reCAPTCHA gagal. Silakan centang 'I'm not a robot'.";
    } else {
        // Ambil data user berdasarkan username
        $stmt = $conn->prepare("SELECT id, username, password, is_verified, is_admin FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $username_db, $password_db, $is_verified, $is_admin);
        $stmt->fetch();

        if($stmt->num_rows == 0){
            $error = "Username belum terdaftar.";
        } else {
            // Verifikasi password
            if(password_verify($password, $password_db)){
                if($is_verified == 0){
                    $error = "Akun belum terverifikasi. Silakan cek email untuk OTP.";
                } else {
                    $_SESSION['id'] = $id;
                    $_SESSION['username'] = $username_db;
                    $_SESSION['is_admin'] = $is_admin;
                    
                    // Cek apakah user adalah admin
                    if ($is_admin) {
                        header("Location: admin_dashboard.php");
                        exit();
                    }
                    
                    // Cek apakah user sudah voting
                    $check_vote = $conn->prepare("SELECT has_voted FROM users WHERE id=?");
                    $check_vote->bind_param("i", $id);
                    $check_vote->execute();
                    $check_vote->bind_result($has_voted);
                    $check_vote->fetch();
                    $check_vote->close();
                    
                    if ($has_voted) {
                        header("Location: sudah_vote.php");
                    } else {
                        header("Location: vote.php");
                    }
                    exit();
                }
            } else {
                $error = "Password salah.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Digital Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #16a085;
            --light: #f9f9f9;
            --dark: #222;
            --success: #27ae60;
            --gray-light: #ecf0f1;
            --gray: #bdc3c7;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(135deg, #f9f9f9, #ecf0f1);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            padding: 20px;
            background-attachment: fixed;
        }
        
        .container {
            width: 100%;
            max-width: 450px;
            background: #fff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        .logo {
            font-size: 2.8rem;
            margin-bottom: 20px;
            color: var(--accent);
        }
        
        h1 {
            font-size: 1.8rem;
            margin-bottom: 25px;
            font-weight: 600;
            color: var(--primary);
            position: relative;
            padding-bottom: 15px;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 3px;
            background: var(--accent);
            border-radius: 2px;
        }
        
        .form-group {
            margin: 20px 0;
            position: relative;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 18px;
            background: var(--gray-light);
            border: 1px solid #ddd;
            border-radius: 8px;
            color: var(--dark);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(22, 160, 133, 0.1);
            background: #fff;
        }
        
        .form-control::placeholder {
            color: var(--gray);
        }
        
        .input-icon {
            position: absolute;
            right: 18px;
            top: 42px;
            color: var(--gray);
        }
        
        .g-recaptcha {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
            width: 100%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transform: translateY(0);
            border: none;
            cursor: pointer;
            background: var(--accent);
            color: white;
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            background: #1abc9c;
        }
        
        .btn i {
            margin-right: 10px;
        }
        
        .footer-text {
            margin-top: 25px;
            font-size: 0.9rem;
            color: var(--secondary);
        }
        
        .footer-text a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-text a:hover {
            color: #1abc9c;
            text-decoration: underline;
        }
        
        .error {
            color: #e74c3c;
            background: rgba(231, 76, 60, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease;
            border-left: 3px solid #e74c3c;
            font-size: 0.9rem;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                border-radius: 10px;
            }
            
            h1 {
                font-size: 1.6rem;
            }
            
            .form-control {
                padding: 12px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-user-shield"></i>
        </div>
        <h1>Masuk ke Sistem</h1>
        
        <?php if(isset($error) && !empty($error)) { echo "<div class='error'><i class='fas fa-exclamation-circle'></i> $error</div>"; } ?>
        
        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required>
                <i class="fas fa-user input-icon"></i>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                <i class="fas fa-lock input-icon"></i>
            </div>
            
            <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
            
            <button type="submit" name="login" class="btn">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>
        
        <p class="footer-text">
            Belum punya akun? <a href="register.php">Daftar disini</a>
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add focus effects to form inputs
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.querySelector('label').style.color = 'var(--accent)';
                    this.parentElement.querySelector('.input-icon').style.color = 'var(--accent)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.querySelector('label').style.color = '';
                    this.parentElement.querySelector('.input-icon').style.color = '';
                });
            });
        });
    </script>
</body>
</html>