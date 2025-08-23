<?php
$conn = new mysqli("localhost", "root", "", "voting_ketua");

$sql = "SELECT kandidat.nama, COUNT(hasil_vote.id) as jumlah_suara
        FROM kandidat
        LEFT JOIN hasil_vote ON kandidat.id = hasil_vote.kandidat_id
        GROUP BY kandidat.id
        ORDER BY jumlah_suara DESC";
$result = $conn->query($sql);

$nama_kandidat = [];
$jumlah_suara = [];
$colors = ['#f72585', '#4361ee', '#4cc9f0', '#3a0ca3', '#7209b7', '#4895ef', '#560bad'];

while ($row = $result->fetch_assoc()) {
    $nama_kandidat[] = $row['nama'];
    $jumlah_suara[] = $row['jumlah_suara'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Voting | Digital Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            color: var(--light);
            padding: 20px;
            background-attachment: fixed;
        }
        
        .container {
            width: 100%;
            max-width: 1000px;
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
            margin-bottom: 30px;
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
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(to right, #fff, #f8f9fa);
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
        
        .description {
            font-size: 1.1rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .results-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
            margin: 30px 0;
        }
        
        .table-container, .chart-container {
            flex: 1;
            min-width: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        th {
            background: rgba(255, 255, 255, 0.1);
            font-weight: 600;
        }
        
        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .winner {
            background: rgba(76, 201, 240, 0.1);
            position: relative;
        }
        
        .winner::after {
            content: '\f091';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            color: var(--accent);
        }
        
        canvas {
            width: 100% !important;
            height: auto !important;
            max-height: 400px;
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
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .results-container {
                flex-direction: column;
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
        <h1><i class="fas fa-chart-pie"></i> Hasil Voting</h1>
        <p class="description">
            Berikut adalah hasil pemilihan ketua kelas yang telah berlangsung.<br>
            Data ditampilkan dalam bentuk tabel dan diagram untuk memudahkan analisis.
        </p>
        
        <div class="results-container">
            <div class="table-container">
                <h3><i class="fas fa-table"></i> Tabel Hasil</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Kandidat</th>
                            <th>Jumlah Suara</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $max_votes = max($jumlah_suara);
                        foreach ($nama_kandidat as $i => $nama): 
                            $is_winner = ($jumlah_suara[$i] == $max_votes && $max_votes > 0);
                        ?>
                        <tr class="<?= $is_winner ? 'winner' : '' ?>">
                            <td><?= htmlspecialchars($nama) ?></td>
                            <td><?= $jumlah_suara[$i] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="chart-container">
                <h3><i class="fas fa-chart-bar"></i> Diagram Hasil</h3>
                <canvas id="voteChart"></canvas>
            </div>
        </div>
        
        <div class="btn-container">
            <a href="hasil.php" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Refresh Hasil
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

            // Chart configuration
            const ctx = document.getElementById('voteChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: <?= json_encode($nama_kandidat) ?>,
                    datasets: [{
                        label: 'Jumlah Suara',
                        data: <?= json_encode($jumlah_suara) ?>,
                        backgroundColor: [
                            '#f72585', '#4361ee', '#4cc9f0', '#3a0ca3', '#7209b7', '#4895ef', '#560bad'
                        ],
                        borderColor: 'rgba(255, 255, 255, 0.3)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#f8f9fa',
                                font: {
                                    family: 'Poppins'
                                }
                            }
                        },
                        tooltip: {
                            bodyFont: {
                                family: 'Poppins'
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>