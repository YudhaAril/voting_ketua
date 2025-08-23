<?php
$conn = new mysqli("localhost", "root", "", "voting_ketua");
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
        
        h2 {
            font-size: 2rem;
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
            font-size: 1rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .kandidat-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .kandidat-option {
            display: none;
        }
        
        .kandidat-label {
            display: flex;
            align-items: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            text-align: left;
            position: relative;
            overflow: hidden;
        }
        
        .kandidat-label:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }
        
        .kandidat-option:checked + .kandidat-label {
            background: rgba(248, 249, 250, 0.1);
            border-color: var(--accent);
            box-shadow: 0 5px 15px rgba(247, 37, 133, 0.2);
        }
        
        .kandidat-option:checked + .kandidat-label::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent);
            font-size: 1.2rem;
        }
        
        .custom-radio {
            width: 22px;
            height: 22px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            margin-right: 15px;
            position: relative;
            flex-shrink: 0;
        }
        
        .kandidat-option:checked + .kandidat-label .custom-radio {
            border-color: var(--accent);
            background: rgba(247, 37, 133, 0.2);
        }
        
        .kandidat-option:checked + .kandidat-label .custom-radio::after {
            content: '';
            position: absolute;
            width: 12px;
            height: 12px;
            background: var(--accent);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        .kandidat-name {
            font-weight: 500;
            font-size: 1.1rem;
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
            font-size: 1rem;
            background: linear-gradient(45deg, var(--accent), #f72585d0);
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(0);
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            background: linear-gradient(45deg, #f72585, #f72585e6);
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
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                border-radius: 15px;
            }
            
            h2 {
                font-size: 1.8rem;
            }
            
            .kandidat-label {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="container">
        <h2>Pemilihan Ketua Kelas</h2>
        <p class="form-description">
            Pilih salah satu kandidat di bawah ini dengan bijak.<br>
            Suara Anda akan menentukan masa depan kelas kita!
        </p>
        
        <form action="proses_vote.php" method="POST">
            <div class="kandidat-container">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <input type="radio" name="kandidat" value="<?= $row['id'] ?>" id="kandidat<?= $row['id'] ?>" class="kandidat-option" required>
                    <label for="kandidat<?= $row['id'] ?>" class="kandidat-label">
                        <span class="custom-radio"></span>
                        <span class="kandidat-name"><?= htmlspecialchars($row['nama']) ?></span>
                    </label>
                <?php endwhile; ?>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Kirim Vote Saya
            </button>
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
        });
    </script>
</body>
</html>