<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pemilihan Ketua Kelas | Digital Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: #7a28cb;         /* Deep purple */
            --secondary: #00d4ff;       /* Electric blue */
            --accent: #ff2a6d;          /* Neon pink */
            --highlight: #05ffa1;       /* Neon green (for glow) */
            --light: #ffffff;
            --dark: #0a0a1a;
            --bg-gradient: linear-gradient(135deg, #12022f, #2c136c, #1e003b);
            --text-glow: #00d4ff;
            --btn-glow: #ff2a6d;
            --particle-color: rgba(255, 255, 255, 0.8);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--light);
            padding: 20px;
            background-attachment: fixed;
            overflow: hidden;
            position: relative;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background: rgba(30, 10, 70, 0.3);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 24px;
            padding: 50px 40px;
            box-shadow: 
                0 10px 30px rgba(0, 0, 0, 0.4),
                0 0 20px rgba(0, 212, 255, 0.2),
                inset 0 0 15px rgba(255, 42, 109, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            text-align: center;
            animation: fadeIn 1s ease-out;
            position: relative;
            z-index: 1;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            border-left: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Glowing corner lines */
        .container::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 1px solid rgba(255, 42, 109, 0.3);
            border-radius: 20px;
            pointer-events: none;
            z-index: -1;
        }

        h1 {
            font-size: 3rem;
            margin-bottom: 15px;
            font-weight: 800;
            background: linear-gradient(45deg, var(--secondary), var(--accent), var(--highlight));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            background-size: 300% 300%;
            animation: gradientShift 4s ease-in-out infinite alternate;
            position: relative;
            display: inline-block;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 5px;
            background: var(--accent);
            border-radius: 3px;
            box-shadow: 0 0 15px var(--accent);
            animation: pulse 2s infinite alternate;
        }

        @keyframes pulse {
            from { opacity: 0.7; transform: translateX(-50%) scale(1); }
            to { opacity: 1; transform: translateX(-50%) scale(1.05); }
        }

        .tagline {
            font-size: 1.2rem;
            margin-bottom: 35px;
            line-height: 1.6;
            color: #d0f4ff;
            text-shadow: 0 0 8px rgba(0, 212, 255, 0.3);
            font-weight: 500;
        }

        .btn-container {
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
            margin: 35px 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 16px 35px;
            border-radius: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            min-width: 220px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            cursor: pointer;
            border: none;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--accent), #ff006e);
            color: white;
            box-shadow: 0 6px 20px rgba(255, 42, 109, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.12);
            color: white;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 6px 20px rgba(0, 212, 255, 0.2);
        }

        .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(255, 42, 109, 0.5);
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #ff2a6d, #ff0055);
            box-shadow: 0 12px 35px rgba(255, 42, 109, 0.6);
            filter: brightness(1.15);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 12px 35px rgba(0, 212, 255, 0.35);
            transform: translateY(-5px);
        }

        .btn i {
            margin-right: 12px;
            font-size: 1.3rem;
        }

        .features {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin: 45px 0 30px;
            gap: 20px;
        }

        .feature {
            flex: 1;
            min-width: 160px;
            max-width: 260px;
            padding: 25px 20px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            transition: all 0.4s ease;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .feature:hover {
            transform: translateY(-8px) scale(1.03);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 30px rgba(255, 42, 109, 0.2);
            border-color: rgba(0, 212, 255, 0.3);
        }

        .feature i {
            font-size: 2.3rem;
            margin-bottom: 16px;
            background: linear-gradient(45deg, var(--highlight), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .feature h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #ffffff;
            font-weight: 700;
        }

        .feature p {
            font-size: 0.9rem;
            opacity: 0.85;
            line-height: 1.5;
        }

        .footer {
            margin-top: 40px;
            font-size: 0.95rem;
            opacity: 0.7;
            text-align: center;
            color: #b8e0ff;
            z-index: 1;
            text-shadow: 0 0 5px rgba(0, 212, 255, 0.3);
        }

        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 5px;
            height: 5px;
            background: var(--particle-color);
            border-radius: 50%;
            box-shadow: 0 0 10px 2px var(--particle-color);
            opacity: 0.7;
            animation: float linear infinite;
            filter: blur(0.5px);
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.7;
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

        @media (max-width: 768px) {
            .container {
                padding: 35px 20px;
                border-radius: 18px;
            }

            h1 {
                font-size: 2.4rem;
            }

            .tagline {
                font-size: 1.05rem;
            }

            .btn {
                min-width: 170px;
                padding: 14px 28px;
                font-size: 1rem;
            }

            .features {
                flex-direction: column;
                align-items: center;
            }

            .feature {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Glowing Particles -->
    <div class="particles" id="particles"></div>

    <!-- Main Content -->
    <div class="container">
        <h1>Pemilihan Ketua Kelas</h1>
        <p class="tagline">
            Sistem pemilihan digital yang aman, transparan, dan efisien.<br>
            Berikan suara Anda untuk menentukan pemimpin kelas terbaik!
        </p>

        <div class="btn-container">
            <a href="vote.php" class="btn btn-primary">
                <i class="fas fa-vote-yea"></i> Mulai Voting
            </a>
            <a href="hasil.php" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> Lihat Hasil
            </a>
        </div>

        <div class="features">
            <div class="feature">
                <i class="fas fa-shield-alt"></i>
                <h3>Aman</h3>
                <p>Proteksi data tingkat tinggi dengan enkripsi modern</p>
            </div>
            <div class="feature">
                <i class="fas fa-mask"></i>
                <h3>Anonim</h3>
                <p>Suara Anda rahasia — tidak ada yang tahu pilihan Anda</p>
            </div>
            <div class="feature">
                <i class="fas fa-tachometer-alt"></i>
                <h3>Cepat</h3>
                <p>Selesai voting dalam 60 detik — hasil langsung terkirim!</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; <?= date('Y') ?> Panitia Pemilihan Ketua Kelas | Digital Voting System
    </div>

    <!-- Script for Particles -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 40;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');

                const size = Math.random() * 6 + 2;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;

                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;

                const duration = Math.random() * 12 + 8;
                particle.style.animationDuration = `${duration}s`;

                particle.style.animationDelay = `${Math.random() * 5}s`;

                particlesContainer.appendChild(particle);
            }
        });
    </script>
</body>
</html>