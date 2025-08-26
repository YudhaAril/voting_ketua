<?php
session_start();
require 'koneksi.php';

// Periksa apakah user adalah admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Query untuk mendapatkan data pengguna (admin dan user biasa dipisah)
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");

// Query untuk admin (username: admin, email: admin@example.com)
$admin_query = "SELECT * FROM users WHERE username = 'admin' AND email = 'admin@example.com'";
$admin_result = $conn->query($admin_query);
$admin_user = $admin_result->fetch_assoc();

// Query untuk user biasa (selain admin utama)
if ($check_column->num_rows == 0) {
    $users_query = "SELECT * FROM users WHERE NOT (username = 'admin' AND email = 'admin@example.com') ORDER BY id DESC";
} else {
    $users_query = "SELECT * FROM users WHERE NOT (username = 'admin' AND email = 'admin@example.com') ORDER BY created_at DESC";
}
$users = $conn->query($users_query);

// Verifikasi pengguna
if (isset($_GET['verify'])) {
    $user_id = $_GET['verify'];
    $conn->query("UPDATE users SET is_verified = 1 WHERE id = $user_id");
    header("Location: kelola_pengguna.php");
    exit();
}

// Reset status voting pengguna tertentu
if (isset($_GET['reset_vote'])) {
    $user_id = $_GET['reset_vote'];
    $conn->query("UPDATE users SET has_voted = 0 WHERE id = $user_id");
    // Hapus juga dari tabel hasil_vote
    $conn->query("DELETE FROM hasil_vote WHERE user_id = $user_id");
    header("Location: kelola_pengguna.php");
    exit();
}

// Reset semua hasil voting
if (isset($_GET['reset_all_votes'])) {
    // Reset status voting semua pengguna
    $conn->query("UPDATE users SET has_voted = 0");
    // Hapus semua data dari tabel hasil_vote
    $conn->query("DELETE FROM hasil_vote");
    header("Location: kelola_pengguna.php");
    exit();
}

// Hapus pengguna
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Cek apakah pengguna yang akan dihapus adalah admin utama
    $user_to_delete = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
    
    if ($user_to_delete['username'] === 'admin' && $user_to_delete['email'] === 'admin@example.com') {
        // Jangan hapus admin utama
        $_SESSION['error'] = "Tidak dapat menghapus akun admin utama.";
        header("Location: kelola_pengguna.php");
        exit();
    }
    
    $conn->query("DELETE FROM users WHERE id = $user_id");
    header("Location: kelola_pengguna.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna | Digital Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Tambahkan style dari admin_dashboard.php atau buat style khusus */
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
            color: var(--light);
            padding: 20px;
            background-attachment: fixed;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        h1 {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(to right, #fff, #f8f9fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border: none;
            cursor: pointer;
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
        
        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #ff9e0b);
            color: var(--dark);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.25);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            overflow: hidden;
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
            background: rgba(255, 255, 255, 0.03);
        }
        
        .admin-row {
            background: rgba(76, 201, 240, 0.1);
        }
        
        .admin-row:hover {
            background: rgba(76, 201, 240, 0.15);
        }
        
        .action-btn {
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            margin-right: 5px;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        .btn-verify {
            background: var(--success);
            color: white;
        }
        
        .btn-reset {
            background: var(--warning);
            color: var(--dark);
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
        }
        
        .btn-delete-disabled {
            background: rgba(255, 107, 107, 0.5);
            color: rgba(255, 255, 255, 0.7);
            cursor: not-allowed;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .verified {
            background: rgba(76, 201, 240, 0.2);
            color: var(--success);
        }
        
        .not-verified {
            background: rgba(255, 190, 11, 0.2);
            color: var(--warning);
        }
        
        .voted {
            background: rgba(76, 201, 240, 0.2);
            color: var(--success);
        }
        
        .not-voted {
            background: rgba(255, 107, 107, 0.2);
            color: var(--danger);
        }
        
        .back-btn {
            margin-bottom: 20px;
        }
        
        .reset-all-container {
            margin: 20px 0;
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
        }
        
        .error-message {
            background: rgba(255, 107, 107, 0.2);
            color: var(--danger);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(255, 107, 107, 0.3);
        }
        
        .success-message {
            background: rgba(76, 201, 240, 0.2);
            color: var(--success);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(76, 201, 240, 0.3);
        }
        
        .section-title {
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-btn">
            <a href="admin_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
        
        <div class="header">
            <h1><i class="fas fa-users-cog"></i> Kelola Pengguna</h1>
            <a href="kelola_pengguna.php" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Refresh
            </a>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="reset-all-container">
            <h3><i class="fas fa-trash-restore"></i> Reset Semua Hasil Voting</h3>
            <p>Tombol ini akan menghapus semua hasil voting dan mengatur ulang status voting semua pengguna.</p>
            <a href="kelola_pengguna.php?reset_all_votes=1" class="btn btn-warning" onclick="return confirmResetAll()">
                <i class="fas fa-trash-restore"></i> Reset Semua Hasil Voting
            </a>
        </div>
        
        <!-- Tampilkan Admin -->
        <h3 class="section-title"><i class="fas fa-crown"></i> Administrator</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Status Verifikasi</th>
                    <th>Status Voting</th>
                    <th>Tanggal Daftar</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($admin_user): ?>
                <tr class="admin-row">
                    <td><?php echo htmlspecialchars($admin_user['username']); ?> <i class="fas fa-crown" style="color: gold; margin-left: 5px;"></i></td>
                    <td><?php echo htmlspecialchars($admin_user['email']); ?></td>
                    <td>
                        <span class="status-badge verified">
                            Terverifikasi
                        </span>
                    </td>
                    <td>
                        <span class="status-badge not-voted">
                            Admin (Tidak Memilih)
                        </span>
                    </td>
                    <td>
                        <?php 
                        if (isset($admin_user['created_at']) && !empty($admin_user['created_at'])) {
                            echo date('d M Y', strtotime($admin_user['created_at']));
                        } else {
                            echo 'Tidak tersedia';
                        }
                        ?>
                    </td>
                    <td>
                        <span class="status-badge verified">
                            Administrator
                        </span>
                    </td>
                    <td>
                        <span class="action-btn btn-delete-disabled">
                            <i class="fas fa-trash"></i> Hapus
                        </span>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Tampilkan Pengguna Biasa -->
        <h3 class="section-title"><i class="fas fa-users"></i> Pengguna Voting</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Status Verifikasi</th>
                    <th>Status Voting</th>
                    <th>Tanggal Daftar</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $user['is_verified'] ? 'verified' : 'not-verified'; ?>">
                            <?php echo $user['is_verified'] ? 'Terverifikasi' : 'Belum Terverifikasi'; ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $user['has_voted'] ? 'voted' : 'not-voted'; ?>">
                            <?php echo $user['has_voted'] ? 'Telah Memilih' : 'Belum Memilih'; ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        if (isset($user['created_at']) && !empty($user['created_at'])) {
                            echo date('d M Y', strtotime($user['created_at']));
                        } else {
                            echo 'Tidak tersedia';
                        }
                        ?>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $user['is_admin'] ? 'verified' : 'not-voted'; ?>">
                            <?php echo $user['is_admin'] ? 'Admin' : 'Pemilih'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!$user['is_verified']): ?>
                            <a href="kelola_pengguna.php?verify=<?php echo $user['id']; ?>" class="action-btn btn-verify">
                                <i class="fas fa-check"></i> Verifikasi
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($user['has_voted']): ?>
                            <a href="kelola_pengguna.php?reset_vote=<?php echo $user['id']; ?>" class="action-btn btn-reset">
                                <i class="fas fa-undo"></i> Reset Vote
                            </a>
                        <?php endif; ?>
                        
                        <a href="kelola_pengguna.php?delete=<?php echo $user['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                            <i class="fas fa-trash"></i> Hapus
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        function confirmResetAll() {
            return confirm("Apakah Anda yakin ingin mereset semua hasil voting? Tindakan ini tidak dapat dibatalkan dan semua data voting akan dihapus.");
        }
    </script>
</body>
</html>