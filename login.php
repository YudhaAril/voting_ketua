<?php
session_start();
require 'koneksi.php';

// Define reCAPTCHA keys
define('RECAPTCHA_SITE_KEY', '6Lc4x6YrAAAAAGe2SeeOGneFKPpGXKItaow9XEXk');
define('RECAPTCHA_SECRET_KEY', '6Lc4x6YrAAAAAMA7gp_a-_if8PP1cEsFWDr_iOcC');

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
        $stmt = $conn->prepare("SELECT id, username, password, is_verified FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $username_db, $password_db, $is_verified);
        $stmt->fetch();

        if($stmt->num_rows == 0){
            $error = "Username belum terdaftar.";
        } else {
            if(password_verify($password, $password_db)){
                if($is_verified == 0){
                    $error = "Akun belum terverifikasi. Silakan cek email untuk OTP.";
                } else {
                    $_SESSION['id'] = $id;
                    $_SESSION['username'] = $username_db;
                    header("Location: index.php"); // redirect ke index.php setelah login
                    exit;
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
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--light);
            padding: 20px;
            background-attachment: fixed;
        }
        
        .container {
            width: 100%;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
            perspective: 1000px;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            z-index: -1;
            animation: rotate 20s linear infinite;
        }
        
        .logo {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--light);
            animation: pulse 2s infinite alternate;
        }
        
        h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(to right, #fff, #f8f9fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--accent);
            border-radius: 2px;
        }
        
        .form-group {
            margin: 25px 0;
            position: relative;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: var(--light);
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(247, 37, 133, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .input-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
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
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
            width: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(0);
            border: none;
            cursor: pointer;
            background: linear-gradient(45deg, var(--accent), #f72585d0);
            color: white;
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            background: linear-gradient(45deg, #f72585, #f72585e6);
        }
        
        .btn i {
            margin-right: 10px;
        }
        
        .footer-text {
            margin-top: 25px;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .footer-text a {
            color: var(--light);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-text a:hover {
            color: var(--accent);
            text-decoration: underline;
        }
        
        .error {
            color: #ff6b6b;
            background: rgba(255, 0, 0, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: shake 0.5s ease;
            border-left: 3px solid #ff6b6b;
        }
        
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: float linear infinite;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            100% {
                transform: scale(1.1);
            }
        }
        
        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            20%, 60% {
                transform: translateX(-5px);
            }
            40%, 80% {
                transform: translateX(5px);
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                border-radius: 15px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .form-control {
                padding: 12px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="container">
        <div class="logo">
            <i class="fas fa-user-shield"></i>
        </div>
        <h1>Masuk ke Sistem</h1>
        
        <?php if(isset($error)) { echo "<div class='error'><i class='fas fa-exclamation-circle'></i> $error</div>"; } ?>
        
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
        // Create floating particles
        document.addEventListener('DOMContentLoaded', function() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random size between 2px and 6px
                const size = Math.random() * 4 + 2;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Random position
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;
                
                // Random animation duration between 10s and 20s
                const duration = Math.random() * 10 + 10;
                particle.style.animationDuration = `${duration}s`;
                
                // Random delay
                particle.style.animationDelay = `${Math.random() * 5}s`;
                
                particlesContainer.appendChild(particle);
            }

            // Add focus effects to form inputs
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.querySelector('label').style.color = 'var(--accent)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.querySelector('label').style.color = '';
                });
            });
        });
    </script>
</body>
</html>