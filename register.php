<?php
require 'koneksi.php';

// Include PHPMailer manual
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

$error = '';
$success = '';

if(isset($_POST['register'])){
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $otp      = rand(100000, 999999); // Generate OTP 6 digit

    // Cek dulu apakah email sudah ada
    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){
        $error = "Email sudah terdaftar. Gunakan email lain.";
    } else {
        // Simpan user + OTP di database
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, otp) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $otp);

        if($stmt->execute()){
            // Kirim OTP via email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'kenasfim@gmail.com'; // ganti emailmu
                $mail->Password = 'gxqm cskp msex olyb';  // ganti App Password Gmail
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('kenasfim@gmail.com', 'Voting Ketua');
                $mail->addAddress($email, $username);

                $mail->isHTML(true);
                $mail->Subject = 'Kode OTP Registrasi';
                $mail->Body    = "Halo <b>$username</b>,<br>Kode OTP Anda: <b>$otp</b>";

                $mail->send();
                $success = "Berhasil daftar! Silakan cek email untuk kode OTP.";
                header("Location: verify.php?email=$email");
                exit();
            } catch (Exception $e) {
                $error = "Gagal mengirim OTP. Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Gagal registrasi: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi | Digital Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            max-width: 500px;
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
        
        .description {
            font-size: 1rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.6;
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
            padding: 15px 20px 15px 45px;
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
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
        }
        
        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
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
        
        .login-link {
            margin-top: 25px;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .login-link a {
            color: var(--light);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .login-link a:hover {
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
        
        .success {
            color: #4ade80;
            background: rgba(76, 201, 240, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 3px solid #4ade80;
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
                padding: 12px 15px 12px 40px;
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="container">
        <div class="logo">
            <i class="fas fa-user-plus"></i>
        </div>
        <h1>Buat Akun Baru</h1>
        <p class="description">
            Daftarkan diri Anda untuk berpartisipasi dalam pemilihan ketua kelas
        </p>
        
        <?php if(!empty($error)): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <div style="position: relative;">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <div style="position: relative;">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan email" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>
            </div>
            
            <button type="submit" name="register" class="btn">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
        </form>
        
        <p class="login-link">
            Sudah punya akun? <a href="login.php">Masuk disini</a>
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

            // Password toggle visibility
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

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