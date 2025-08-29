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
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #16a085;
            --light: #f9f9f9;
            --dark: #222;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
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
            color: var(--dark);
            padding: 20px;
            background-attachment: fixed;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        h1 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        h2 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: none;
            cursor: pointer;
            gap: 8px;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            margin-right: 5px;
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
        }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .kandidat-foto {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid var(--gray-light);
        }
        
        .back-btn {
            margin-bottom: 20px;
        }
        
        .form-container {
            background: var(--gray-light);
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--gray);
            background: white;
            color: var(--dark);
            font-family: 'Poppins', Arial, sans-serif;
            transition: all 0.3s ease;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(22, 160, 133, 0.1);
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .submit-btn {
            background: var(--accent);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            background: #1abc9c;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 3px solid;
        }
        
        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            border-color: var(--success);
            color: var(--success);
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            border-color: var(--danger);
            color: var(--danger);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: var(--secondary);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--gray);
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            th, td {
                padding: 10px;
                font-size: 0.9rem;
            }
            
            .kandidat-foto {
                width: 60px;
                height: 60px;
            }
            
            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }
            
            .action-btn {
                margin-right: 0;
                justify-content: center;
            }
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
                <i class="fas fa-check-circle"></i> Kandidat berhasil ditambahkan!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Kandidat berhasil dihapus!
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="header">
            <h1><i class="fas fa-user-tie"></i> Kelola Kandidat</h1>
            <a href="kelola_kandidat.php" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Refresh
            </a>
        </div>
        
        <div class="form-container">
            <h2><i class="fas fa-plus-circle"></i> Tambah Kandidat Baru</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama">Nama Kandidat</label>
                    <input type="text" id="nama" name="nama" required placeholder="Masukkan nama kandidat">
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
                    <textarea id="visi" name="visi" required placeholder="Masukkan visi kandidat"></textarea>
                </div>
                <?php endif; ?>
                
                <?php if ($columns_exist['misi']): ?>
                <div class="form-group">
                    <label for="misi">Misi</label>
                    <textarea id="misi" name="misi" required placeholder="Masukkan misi kandidat"></textarea>
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
                                <div style="width: 80px; height: 80px; background: var(--gray-light); display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                                    <i class="fas fa-user" style="font-size: 2rem; color: var(--gray);"></i>
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