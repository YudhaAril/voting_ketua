<?php
require 'koneksi.php';

if(isset($_GET['email'])){
    $email = $_GET['email'];
}

$error = '';
$success = '';

if(isset($_POST['verify'])){
    $otp_input = $_POST['otp'];
    $email     = $_POST['email'];

    $stmt = $conn->prepare("SELECT otp FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($otp_db);
    $stmt->fetch();
    $stmt->close();

    if($otp_input == $otp_db){
        $update = $conn->prepare("UPDATE users SET is_verified=1, otp=NULL WHERE email=?");
        $update->bind_param("s", $email);
        if($update->execute()){
            $success = "Verifikasi berhasil! Akun Anda aktif.";
        } else {
            $error = "Gagal mengupdate status verifikasi.";
        }
    } else {
        $error = "Kode OTP salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP | Digital Voting System</title>
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
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: var(--light);
            font-size: 1.2rem;
            letter-spacing: 5px;
            text-align: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(247, 37, 133, 0.2);
            background: rgba(255, 255, 255, 0.15);
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
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .btn i {
            margin-right: 10px;
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
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="container">
        <div class="logo">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h1>Verifikasi OTP</h1>
        <p class="description">
            Kami telah mengirim kode OTP ke email Anda<br>
            Silakan masukkan kode tersebut di bawah ini
        </p>
        
        <?php if(!empty($error)): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="otp">Kode OTP</label>
                <input type="text" id="otp" name="otp" class="form-control" placeholder="Masukkan 6 digit kode" required maxlength="6" pattern="\d{6}" title="Masukkan 6 digit angka">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            </div>
            
            <button type="submit" name="verify" class="btn">
                <i class="fas fa-check"></i> Verifikasi
            </button>
            
            <a href="login.php" class="btn btn-secondary" style="margin-top: 15px;">
                <i class="fas fa-sign-in-alt"></i> Kembali ke Login
            </a>
        </form>
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

            // Auto focus on OTP input
            document.getElementById('otp').focus();
            
            // Auto move to next digit (if implementing multiple input fields)
            // This can be enhanced for better OTP input experience
        });
    </script>
</body>
</html>