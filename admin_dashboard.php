<?php
session_start();
require 'koneksi.php';

// Periksa apakah user adalah admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Ambil statistik untuk ditampilkan
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$total_votes = $conn->query("SELECT COUNT(*) FROM hasil_vote")->fetch_row()[0];
$verified_users = $conn->query("SELECT COUNT(*) FROM users WHERE is_verified = 1")->fetch_row()[0];
$voted_users = $conn->query("SELECT COUNT(*) FROM users WHERE has_voted = 1")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Digital Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-gradient: linear-gradient(135deg, #4361ee, #3a0ca3);
            --secondary: #3f37c9;
            --accent: #f72585;
            --accent-gradient: linear-gradient(135deg, #f72585, #b5179e);
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --warning: #ffbe0b;
            --danger: #ff6b6b;
            --card-bg: rgba(255, 255, 255, 0.1);
            --card-border: rgba(255, 255, 255, 0.15);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--light);
            padding: 20px;
            background-attachment: fixed;
        }
        
        .dashboard-container {
            width: 100%;
            max-width: 1200px;
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.8s ease-out;
        }
        
        .dashboard-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            z-index: -1;
            animation: rotate 30s linear infinite;
        }
        
        .header {
            margin-bottom: 40px;
            position: relative;
        }
        
        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--accent-gradient);
            padding: 10px 20px;
            border-radius: 50px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(247, 37, 133, 0.3);
            animation: pulse 2s infinite;
        }
        
        h1 {
            font-size: 2.8rem;
            margin-bottom: 10px;
            font-weight: 800;
            background: linear-gradient(to right, #fff, #f8f9fa, #f72585);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            display: inline-block;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--accent);
            border-radius: 2px;
        }
        
        .welcome-text {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-top: 20px;
        }
        
        .welcome-text span {
            color: var(--accent);
            font-weight: 600;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--accent-gradient);
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .users-icon { color: var(--primary); }
        .votes-icon { color: var(--success); }
        .verified-icon { color: var(--accent); }
        .voted-icon { color: var(--warning); }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(to right, #fff, #f8f9fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 30px 25px;
            text-align: center;
            transition: all 0.4s ease;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, transparent 100%);
            z-index: -1;
        }
        
        .feature-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            padding: 15px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .feature-card h3 {
            font-size: 1.4rem;
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        .feature-card p {
            font-size: 0.95rem;
            opacity: 0.8;
            line-height: 1.6;
        }
        
        .action-buttons {
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
            padding: 16px 32px;
            border-radius: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
            min-width: 220px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(0);
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
        }
        
        .btn-primary {
            background: var(--accent-gradient);
            color: white;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #f72585, #b5179e);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .btn i {
            margin-right: 12px;
            font-size: 1.2rem;
        }
        
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
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
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 5px 15px rgba(247, 37, 133, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 8px 25px rgba(247, 37, 133, 0.5);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 5px 15px rgba(247, 37, 133, 0.3);
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
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 30px 20px;
                border-radius: 20px;
            }
            
            h1 {
                font-size: 2.2rem;
            }
            
            .stats-grid,
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .btn {
                min-width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="dashboard-container">
        <div class="header">
            <div class="admin-badge">
                <i class="fas fa-crown"></i>
                <span>Administrator</span>
            </div>
            <h1>Control Panel</h1>
            <p class="welcome-text">Selamat datang, <span><?php echo $_SESSION['username']; ?></span></p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Pengguna</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon votes-icon">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <div class="stat-number"><?php echo $total_votes; ?></div>
                <div class="stat-label">Total Voting</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon verified-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo $verified_users; ?></div>
                <div class="stat-label">Terverifikasi</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon voted-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-number"><?php echo $voted_users; ?></div>
                <div class="stat-label">Telah Memilih</div>
            </div>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3>Hasil Voting</h3>
                <p>Pantau hasil pemilihan secara real-time dengan grafik interaktif dan analisis data</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h3>Kelola Pengguna</h3>
                <p>Kelola data pengguna, verifikasi akun, dan reset status voting jika diperlukan</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Kelola Kandidat</h3>
                <p>Tambah, edit, atau hapus kandidat pemilihan sesuai kebutuhan</p>
            </div>
        </div>
        
        <!-- Ganti bagian action-buttons dengan kode berikut -->
<div class="action-buttons">
    <a href="hasil.php" class="btn btn-primary">
        <i class="fas fa-chart-bar"></i> Lihat Hasil
    </a>
    <a href="kelola_pengguna.php" class="btn btn-secondary">
        <i class="fas fa-users-cog"></i> Kelola Pengguna
    </a>
    <a href="kelola_kandidat.php" class="btn btn-secondary">
        <i class="fas fa-user-tie"></i> Kelola Kandidat
    </a>
    <a href="logout.php" class="btn btn-secondary">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

    <script>
        // Create floating particles
        document.addEventListener('DOMContentLoaded', function() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 40;
            
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

            // Add hover effects to cards
            const cards = document.querySelectorAll('.stat-card, .feature-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.zIndex = '10';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.zIndex = '1';
                });
            });
        });
    </script>
</body>
</html>