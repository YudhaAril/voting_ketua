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
            padding: 14px 20px;
            background: var(--gray-light);
            border: 1px solid #ddd;
            border-radius: 8px;
            color: var(--dark);
            font-size: 1.2rem;
            letter-spacing: 5px;
            text-align: center;
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
            letter-spacing: normal;
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
        
        .btn-secondary {
            background: var(--secondary);
            color: white;
        }
        
        .btn-secondary:hover {
            background: #2c3e50;
        }
        
        .btn i {
            margin-right: 10px;
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
                padding: 12px 15px;
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Auto focus on OTP input
            document.getElementById('otp').focus();
            
            // Auto move to next digit (if implementing multiple input fields)
            // This can be enhanced for better OTP input experience
            
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