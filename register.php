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
            max-width: 500px;
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
        
        .description {
            font-size: 1rem;
            margin-bottom: 30px;
            color: var(--secondary);
            line-height: 1.6;
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
            padding: 14px 18px 14px 45px;
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
            left: 18px;
            top: 42px;
            color: var(--gray);
        }
        
        .password-toggle {
            position: absolute;
            right: 18px;
            top: 42px;
            color: var(--gray);
            cursor: pointer;
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
        
        .login-link {
            margin-top: 25px;
            font-size: 0.9rem;
            color: var(--secondary);
        }
        
        .login-link a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .login-link a:hover {
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
        
        .success {
            color: #27ae60;
            background: rgba(39, 174, 96, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 3px solid #27ae60;
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
                padding: 12px 15px 12px 40px;
            }
        }
    </style>
</head>
<body>
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
        document.addEventListener('DOMContentLoaded', function() {
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