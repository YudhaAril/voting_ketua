<?php
session_start();
require 'koneksi.php';

// Periksa apakah user sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$conn = new mysqli("localhost", "root", "", "voting_ketua");

// Periksa apakah user sudah memilih sebelumnya
$check_vote = $conn->prepare("SELECT has_voted FROM users WHERE id = ?");
$check_vote->bind_param("i", $user_id);
$check_vote->execute();
$check_vote->bind_result($has_voted);
$check_vote->fetch();
$check_vote->close();

if ($has_voted) {
    die("Anda sudah melakukan voting sebelumnya. Hanya boleh memilih sekali.");
}

if (isset($_POST['kandidat'])) {
    $id_kandidat = $_POST['kandidat'];
    
    // Mulai transaction
    $conn->begin_transaction();
    
    try {
        // Simpan vote
        $stmt = $conn->prepare("INSERT INTO hasil_vote (kandidat_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_kandidat, $user_id);
        $stmt->execute();
        
        // Tandai user sudah memilih
        $update_user = $conn->prepare("UPDATE users SET has_voted = 1 WHERE id = ?");
        $update_user->bind_param("i", $user_id);
        $update_user->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect ke halaman terima kasih
        header("Location: terima_kasih.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction jika ada error
        $conn->rollback();
        die("Terjadi kesalahan: " . $e->getMessage());
    }
} else {
    die("Anda belum memilih kandidat.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terima Kasih | Digital Voting System</title>
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
            max-width: 600px;
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
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            background: linear-gradient(to right, #fff, #f8f9fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .success-icon {
            font-size: 5rem;
            color: #4ade80;
            margin: 20px 0;
            animation: bounce 1s ease infinite alternate;
        }
        
        .thank-you-message {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .btn-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 30px;
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
            min-width: 200px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(0);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, var(--accent), #f72585d0);
            color: white;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #f72585, #f72585e6);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .btn i {
            margin-right: 10px;
            font-size: 1.2rem;
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
        
        @keyframes bounce {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(-15px);
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                border-radius: 15px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .btn {
                min-width: 160px;
                padding: 12px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Terima Kasih!</h1>
        <p class="thank-you-message">
            Suara Anda telah tercatat dengan aman dalam sistem kami.<br>
            Partisipasi Anda sangat berarti untuk kemajuan kelas kita.
        </p>
        
        <div class="btn-container">
            <a href="hasil.php" class="btn btn-primary">
                <i class="fas fa-chart-bar"></i> Lihat Hasil Voting
            </a>
            <a href="login.php" class="btn btn-secondary">
                <i class="fas fa-sign-in-alt"></i> Kembali ke Login
            </a>
        </div>
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
        });
    </script>
</body>
</html>