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
$colors = ['#16a085', '#2c3e50', '#34495e', '#1abc9c', '#27ae60', '#2980b9', '#8e44ad'];

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
            max-width: 1000px;
            background: #fff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 2.8rem;
            margin-bottom: 20px;
            color: var(--accent);
        }
        
        h1 {
            font-size: 2rem;
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
        
        h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: var(--secondary);
            font-weight: 600;
        }
        
        .description {
            font-size: 1rem;
            margin-bottom: 30px;
            color: var(--secondary);
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
            background: var(--gray-light);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }
        
        th {
            background: var(--accent);
            color: white;
            font-weight: 600;
        }
        
        tr:hover {
            background: rgba(22, 160, 133, 0.05);
        }
        
        .winner {
            background: rgba(22, 160, 133, 0.1);
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
            padding: 14px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
            min-width: 180px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transform: translateY(0);
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: var(--accent);
            color: white;
        }
        
        .btn-secondary {
            background: var(--secondary);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary:hover {
            background: #1abc9c;
        }
        
        .btn-secondary:hover {
            background: #2c3e50;
        }
        
        .btn i {
            margin-right: 10px;
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
    <div class="container">
        <div class="logo">
            <i class="fas fa-chart-pie"></i>
        </div>
        <h1>Hasil Voting</h1>
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
        // Chart configuration
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('voteChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: <?= json_encode($nama_kandidat) ?>,
                    datasets: [{
                        label: 'Jumlah Suara',
                        data: <?= json_encode($jumlah_suara) ?>,
                        backgroundColor: [
                            '#16a085', '#2c3e50', '#34495e', '#1abc9c', '#27ae60', '#2980b9', '#8e44ad'
                        ],
                        borderColor: 'white',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
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