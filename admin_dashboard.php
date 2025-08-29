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
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js (untuk fitur hasil.php nanti) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary: #1a3a5f;
            --accent: #00c9b1;
            --secondary: #34495e;
            --light: #f9f9f9;
            --dark: #222;
            --success: #27ae60;
            --gray-light: #ecf0f1;
            --card-bg: #ffffff;
            --shadow: rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #e4edf5);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(26, 58, 95, 0.03) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(0, 201, 177, 0.04) 0%, transparent 20%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            padding: 20px;
            background-attachment: fixed;
        }

        .dashboard-container {
            width: 100%;
            max-width: 1200px;
            background: var(--card-bg);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
            position: relative;
            overflow: hidden;
            border: 1px solid #eaeaea;
        }

        .live-badge {
            position: absolute;
            top: 20px;
            right: 40px;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(39, 174, 96, 0.3);
        }

        .clock {
            font-size: 0.9rem;
            opacity: 0.7;
            margin-top: 5px;
            font-weight: 500;
        }

        .header {
            margin-bottom: 40px;
            position: relative;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--primary), #294e76);
            padding: 10px 20px;
            border-radius: 50px;
            margin-bottom: 12px;
            box-shadow: 0 6px 18px rgba(26, 58, 95, 0.2);
            color: white;
            font-weight: 600;
        }

        h1 {
            font-size: 3.2rem;
            margin-bottom: 8px;
            font-weight: 800;
            color: var(--primary);
            position: relative;
            display: inline-block;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 5px;
            background: var(--accent);
            border-radius: 3px;
            box-shadow: 0 3px 10px rgba(0, 201, 177, 0.4);
        }

        .welcome-text {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-top: 15px;
            color: var(--secondary);
        }

        .welcome-text span {
            color: var(--accent);
            font-weight: 700;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: linear-gradient(135deg, #ffffff, #f8f9fc);
            border: 1px solid #e0e0e0;
            border-radius: 14px;
            padding: 30px 25px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            box-shadow: 0 6px 20px var(--shadow);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: var(--accent);
            border-radius: 14px 14px 0 0;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
            border-color: var(--accent);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 2rem;
            background: var(--gray-light);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1);
        }

        .users-icon { color: var(--primary); }
        .votes-icon { color: var(--success); }
        .verified-icon { color: var(--accent); }
        .voted-icon { color: #f39c12; }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--primary);
            font-family: 'Inter', monospace;
        }

        .stat-label {
            font-size: 0.95rem;
            opacity: 0.85;
            color: var(--secondary);
            font-weight: 500;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .feature-card {
            background: var(--card-bg);
            border: 1px solid #eaeaea;
            border-radius: 14px;
            padding: 32px 25px;
            text-align: center;
            transition: all 0.4s ease;
            box-shadow: 0 6px 20px var(--shadow);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
            border-color: var(--accent);
        }

        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
            color: var(--accent);
            display: inline-block;
            padding: 18px;
            border-radius: 20px;
            background: var(--gray-light);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            transition: transform 0.4s ease;
        }

        .feature-card:hover .feature-icon {
            transform: translateY(-5px) scale(1.05);
            animation: bounce 0.6s ease infinite alternate;
        }

        @keyframes bounce {
            0% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-8px) scale(1.08); }
            100% { transform: translateY(-5px) scale(1.05); }
        }

        .feature-card h3 {
            font-size: 1.45rem;
            margin-bottom: 14px;
            font-weight: 700;
            color: var(--primary);
        }

        .feature-card p {
            font-size: 0.95rem;
            opacity: 0.85;
            line-height: 1.65;
            color: var(--secondary);
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
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
            min-width: 230px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(0);
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00c9b1, #00a0e3);
            color: white;
        }

        .btn-secondary {
            background: var(--gray-light);
            color: var(--primary);
            border: 1px solid #ddd;
        }

        .btn:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.18);
        }

        .btn-primary:hover i {
            transform: translateX(5px);
        }

        .btn-primary i, .btn-secondary i {
            transition: transform 0.3s ease;
        }

        .btn-secondary:hover {
            background: #dfe6e9;
        }

        .btn i {
            margin-right: 12px;
            font-size: 1.2rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 30px 20px;
                border-radius: 14px;
            }

            h1 {
                font-size: 2.4rem;
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

            .live-badge {
                position: static;
                margin-bottom: 15px;
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Live Mode Badge -->
        <div class="live-badge">
            <i class="fas fa-circle" style="font-size: 0.6rem; margin-right: 4px;"></i>
            Live Mode
        </div>

        <div class="header">
            <div class="admin-badge">
                <i class="fas fa-crown"></i>
                <span>Administrator</span>
            </div>
            <h1>Control Panel</h1>
            <p class="welcome-text">Selamat datang, <span><?php echo htmlspecialchars($_SESSION['username']); ?></span></p>
            <p class="clock" id="current-time">Loading...</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number" id="total-users">0</div>
                <div class="stat-label">Total Pengguna</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon votes-icon">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <div class="stat-number" id="total-votes">0</div>
                <div class="stat-label">Total Voting</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon verified-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number" id="verified-users">0</div>
                <div class="stat-label">Terverifikasi</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon voted-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-number" id="voted-users">0</div>
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
    </div>

    <script>
        // Format angka dengan efek count-up
        function animateValue(id, start, end, duration) {
            const obj = document.getElementById(id);
            const range = end - start;
            const increment = range / (duration / 16);
            let current = start;
            const timer = setInterval(() => {
                current += increment;
                if (current >= end) {
                    obj.textContent = end;
                    clearInterval(timer);
                } else {
                    obj.textContent = Math.floor(current);
                }
            }, 16);
        }

        // Update jam real-time
        function updateClock() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('current-time').textContent = now.toLocaleDateString('id-ID', options);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Jalankan animasi angka
            animateValue("total-users", 0, <?php echo $total_users; ?>, 1500);
            animateValue("total-votes", 0, <?php echo $total_votes; ?>, 1500);
            animateValue("verified-users", 0, <?php echo $verified_users; ?>, 1500);
            animateValue("voted-users", 0, <?php echo $voted_users; ?>, 1500);

            // Update jam setiap detik
            updateClock();
            setInterval(updateClock, 1000);

            // Efek hover card
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