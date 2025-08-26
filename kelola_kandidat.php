<?php
session_start();
require 'koneksi.php';

// Periksa apakah user adalah admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Periksa apakah kolom yang diperlukan ada di tabel kandidat
$columns_to_check = ['foto', 'visi', 'misi'];
$columns_exist = [];

foreach ($columns_to_check as $column) {
    $check_column = $conn->query("SHOW COLUMNS FROM kandidat LIKE '$column'");
    $columns_exist[$column] = ($check_column->num_rows > 0);
    
    // Jika kolom tidak ada, tambahkan
    if (!$columns_exist[$column]) {
        if ($column === 'foto') {
            $conn->query("ALTER TABLE kandidat ADD COLUMN $column VARCHAR(255) DEFAULT ''");
        } else {
            $conn->query("ALTER TABLE kandidat ADD COLUMN $column TEXT");
        }
        $columns_exist[$column] = true;
    }
}

// Query untuk mendapatkan data kandidat
$kandidat = $conn->query("SELECT * FROM kandidat ORDER BY id DESC");

// Tambah kandidat
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    $visi = trim($_POST['visi']);
    $misi = trim($_POST['misi']);
    
    // Handle upload foto hanya jika kolom foto ada
    $foto_name = '';
    if ($columns_exist['foto']) {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['foto']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $foto_name = time() . '_' . basename($_FILES['foto']['name']);
                $upload_path = 'uploads/' . $foto_name;
                
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0755, true);
                }
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                    // File berhasil diupload
                } else {
                    $foto_name = '';
                    $error_message = "Gagal mengupload foto.";
                }
            } else {
                $error_message = "Hanya file gambar (JPEG, PNG, GIF) yang diizinkan.";
            }
        }
    }
    
    // Gunakan prepared statement untuk keamanan
    if ($columns_exist['foto']) {
        $stmt = $conn->prepare("INSERT INTO kandidat (nama, foto, visi, misi) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $foto_name, $visi, $misi);
    } else {
        // Fallback jika kolom foto tidak ada
        $stmt = $conn->prepare("INSERT INTO kandidat (nama, visi, misi) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $visi, $misi);
    }
    
    if ($stmt->execute()) {
        header("Location: kelola_kandidat.php?success=1");
        exit();
    } else {
        $error_message = "Gagal menambahkan kandidat: " . $conn->error;
    }
}

// Hapus kandidat
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        // Hapus foto jika ada
        if ($columns_exist['foto']) {
            $result = $conn->query("SELECT foto FROM kandidat WHERE id = $id");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (!empty($row['foto']) && file_exists('uploads/' . $row['foto'])) {
                    unlink('uploads/' . $row['foto']);
                }
            }
        }
        
        // Hapus data vote yang terkait dengan kandidat ini terlebih dahulu
        $conn->query("DELETE FROM hasil_vote WHERE kandidat_id = $id");
        
        // Hapus kandidat
        $conn->query("DELETE FROM kandidat WHERE id = $id");
        
        // Commit transaksi
        $conn->commit();
        
        header("Location: kelola_kandidat.php?deleted=1");
        exit();
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        $conn->rollback();
        $error_message = "Gagal menghapus kandidat: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kandidat | Digital Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS styles remain the same as in your original file */
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
        
        .action-btn {
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            margin-right: 5px;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
        }
        
        .kandidat-foto {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .back-btn {
            margin-bottom: 20px;
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        input, textarea {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-family: 'Poppins', Arial, sans-serif;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .submit-btn {
            background: var(--success);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background: rgba(76, 201, 240, 0.2);
            border: 1px solid var(--success);
        }
        
        .alert-error {
            background: rgba(255, 107, 107, 0.2);
            border: 1px solid var(--danger);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
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
        
        <!-- Notifikasi -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> &nbsp; Kandidat berhasil ditambahkan!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> &nbsp; Kandidat berhasil dihapus!
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> &nbsp; <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="header">
            <h1><i class="fas fa-user-tie"></i> Kelola Kandidat</h1>
            <a href="kelola_kandidat.php" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Refresh
            </a>
        </div>
        
        <div class="form-container">
            <h2>Tambah Kandidat Baru</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama">Nama Kandidat</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                
                <?php if ($columns_exist['foto']): ?>
                <div class="form-group">
                    <label for="foto">Foto Kandidat</label>
                    <input type="file" id="foto" name="foto" accept="image/*">
                </div>
                <?php endif; ?>
                
                <?php if ($columns_exist['visi']): ?>
                <div class="form-group">
                    <label for="visi">Visi</label>
                    <textarea id="visi" name="visi" required></textarea>
                </div>
                <?php endif; ?>
                
                <?php if ($columns_exist['misi']): ?>
                <div class="form-group">
                    <label for="misi">Misi</label>
                    <textarea id="misi" name="misi" required></textarea>
                </div>
                <?php endif; ?>
                
                <button type="submit" name="tambah" class="submit-btn">
                    <i class="fas fa-plus"></i> Tambah Kandidat
                </button>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <?php if ($columns_exist['foto']): ?>
                    <th>Foto</th>
                    <?php endif; ?>
                    <th>Nama</th>
                    <?php if ($columns_exist['visi']): ?>
                    <th>Visi</th>
                    <?php endif; ?>
                    <?php if ($columns_exist['misi']): ?>
                    <th>Misi</th>
                    <?php endif; ?>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($kandidat->num_rows > 0): ?>
                    <?php while ($row = $kandidat->fetch_assoc()): ?>
                    <tr>
                        <?php if ($columns_exist['foto']): ?>
                        <td>
                            <?php if (!empty($row['foto'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($row['foto']); ?>" alt="<?php echo htmlspecialchars($row['nama']); ?>" class="kandidat-foto">
                            <?php else: ?>
                                <div style="width: 80px; height: 80px; background: #ccc; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                                    <i class="fas fa-user" style="font-size: 2rem; color: #666;"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <td><?php echo htmlspecialchars($row['nama'] ?? '-'); ?></td>
                        <?php if ($columns_exist['visi']): ?>
                        <td><?php echo htmlspecialchars($row['visi'] ?? '-'); ?></td>
                        <?php endif; ?>
                        <?php if ($columns_exist['misi']): ?>
                        <td><?php echo htmlspecialchars($row['misi'] ?? '-'); ?></td>
                        <?php endif; ?>
                        <td>
                            <a href="kelola_kandidat.php?delete=<?php echo $row['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Yakin ingin menghapus kandidat ini? Semua data vote untuk kandidat ini juga akan dihapus.')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php 
                            $colspan = 1; // Nama column
                            $colspan += $columns_exist['foto'] ? 1 : 0;
                            $colspan += $columns_exist['visi'] ? 1 : 0;
                            $colspan += $columns_exist['misi'] ? 1 : 0;
                            $colspan += 1; // Aksi column
                            echo $colspan;
                        ?>">
                            <div class="empty-state">
                                <i class="fas fa-user-slash"></i>
                                <h3>Belum ada kandidat</h3>
                                <p>Tambahkan kandidat baru menggunakan form di atas</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <script>
        // Menghilangkan notifikasi otomatis setelah 5 detik
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>