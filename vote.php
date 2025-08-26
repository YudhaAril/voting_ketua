<?php
session_start();
require 'koneksi.php';

// Periksa apakah user sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Periksa apakah user adalah admin
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    header("Location: admin_dashboard.php");
    exit();
}

// Periksa apakah user sudah memilih
$user_id = $_SESSION['id'];
$check_vote = $conn->prepare("SELECT has_voted FROM users WHERE id = ?");
$check_vote->bind_param("i", $user_id);
$check_vote->execute();
$check_vote->bind_result($has_voted);
$check_vote->fetch();
$check_vote->close();

if ($has_voted) {
    header("Location: sudah_vote.php");
    exit();
}

// Query untuk mendapatkan data kandidat lengkap (termasuk foto, visi, misi)
$result = $conn->query("SELECT * FROM kandidat");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Ketua Kelas | Digital Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --card-bg: rgba(255, 255, 255, 0.07);
            --card-border: rgba(255, 255, 255, 0.1);
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
            max-width: 1200px;
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
        
        h2 {
            font-size: 2.5rem;
            margin-bottom: 25px;
            font-weight: 700;
            background: linear-gradient(to right, #fff, #f8f9fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            display: inline-block;
        }
        
        h2::after {
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
        
        .form-description {
            font-size: 1.1rem;
            margin-bottom: 40px;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .kandidat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .kandidat-option {
            display: none;
        }
        
        .kandidat-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 0;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            text-align: left;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .kandidat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .kandidat-option:checked + .kandidat-card {
            border-color: var(--accent);
            box-shadow: 0 5px 15px rgba(247, 37, 133, 0.3);
            background: rgba(248, 249, 250, 0.1);
        }
        
        .kandidat-option:checked + .kandidat-card::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            top: 15px;
            width: 30px;
            height: 30px;
            background: var(--accent);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        
        .kandidat-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 14px;
            border-top-right-radius: 14px;
        }
        
        .kandidat-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .kandidat-name {
            font-weight: 600;
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: white;
        }
        
        .kandidat-detail {
            margin-top: 15px;
        }
        
        .detail-section {
            margin-bottom: 15px;
        }
        
        .detail-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }
        
        .detail-title i {
            transition: transform 0.3s ease;
        }
        
        .detail-content {
            font-size: 0.95rem;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 8px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }
        
        .detail-checkbox {
            display: none;
        }
        
        .detail-checkbox:checked ~ .detail-content {
            max-height: 500px;
            padding: 10px;
        }
        
        .detail-checkbox:checked ~ .detail-title i {
            transform: rotate(180deg);
        }
        
        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            background: linear-gradient(45deg, var(--accent), #f72585d0);
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(0);
            margin-top: 20px;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            background: linear-gradient(45deg, #f72585, #f72585e6);
        }
        
        .btn-submit:disabled {
            background: rgba(255, 255, 255, 0.2);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-submit i {
            margin-right: 10px;
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
        
        .no-candidates {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                border-radius: 15px;
            }
            
            h2 {
                font-size: 2rem;
            }
            
            .kandidat-grid {
                grid-template-columns: 1fr;
            }
            
            .form-description {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="container">
        <h2>Pemilihan Ketua Kelas</h2>
        <p class="form-description">
            Pilih salah satu kandidat di bawah ini dengan bijak. Klik pada kartu kandidat untuk memilih, 
            dan perluas bagian visi & misi untuk mengetahui lebih detail program mereka.<br>
            Suara Anda akan menentukan masa depan kelas kita!
        </p>
        
        <form action="proses_vote.php" method="POST" id="voteForm">
            <div class="kandidat-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <input type="radio" name="kandidat" value="<?= $row['id'] ?>" id="kandidat<?= $row['id'] ?>" class="kandidat-option" required>
                        <label for="kandidat<?= $row['id'] ?>" class="kandidat-card">
                            <?php if (!empty($row['foto'])): ?>
                                <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" alt="Foto <?= htmlspecialchars($row['nama']) ?>" class="kandidat-image">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/350x200/3a0ca3/ffffff?text=<?= urlencode($row['nama']) ?>" alt="Foto <?= htmlspecialchars($row['nama']) ?>" class="kandidat-image">
                            <?php endif; ?>
                            
                            <div class="kandidat-info">
                                <div class="kandidat-name"><?= htmlspecialchars($row['nama']) ?></div>
                                
                                <div class="kandidat-detail">
                                    <?php if (!empty($row['visi'])): ?>
                                    <div class="detail-section">
                                        <input type="checkbox" class="detail-checkbox" id="visi<?= $row['id'] ?>">
                                        <label for="visi<?= $row['id'] ?>" class="detail-title">
                                            <span>Visi</span>
                                            <i class="fas fa-chevron-down"></i>
                                        </label>
                                        <div class="detail-content"><?= nl2br(htmlspecialchars($row['visi'])) ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($row['misi'])): ?>
                                    <div class="detail-section">
                                        <input type="checkbox" class="detail-checkbox" id="misi<?= $row['id'] ?>">
                                        <label for="misi<?= $row['id'] ?>" class="detail-title">
                                            <span>Misi</span>
                                            <i class="fas fa-chevron-down"></i>
                                        </label>
                                        <div class="detail-content"><?= nl2br(htmlspecialchars($row['misi'])) ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </label>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-candidates">
                        <i class="fas fa-users fa-3x" style="margin-bottom: 20px;"></i>
                        <h3>Belum Ada Kandidat</h3>
                        <p>Silakan hubungi administrator untuk menambahkan kandidat.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
            <button type="submit" class="btn-submit" id="submitButton">
                <i class="fas fa-paper-plane"></i> Kirim Vote Saya
            </button>
            <?php endif; ?>
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
            
            // Prevent detail checkboxes from affecting the card selection
            const detailCheckboxes = document.querySelectorAll('.detail-checkbox');
            detailCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });
            
            // Handle card selection
            const kandidatOptions = document.querySelectorAll('.kandidat-option');
            kandidatOptions.forEach(option => {
                option.addEventListener('change', function() {
                    // Enable submit button when a candidate is selected
                    document.getElementById('submitButton').disabled = false;
                });
            });
            
            // Initially disable submit button
            document.getElementById('submitButton').disabled = true;
        });
    </script>
</body>
</html>