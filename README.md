## STRUKTUR PROYEK

voting_ketua/
├── config.php          # Konfigurasi dasar (kemungkinan database)
├── hasil.php           # Halaman untuk menampilkan hasil voting
├── index.php           # Halaman utama (landing page / login form)
├── koneksi.php         # File koneksi database MySQL
├── login.php           # Halaman login
├── login_proses.php    # Proses autentikasi login
├── logout.php          # Logout session
├── mailer.php          # Script pengiriman email (OTP/Verifikasi)
├── proses_vote.php     # Script proses voting setelah login
├── register.php        # Halaman registrasi user
├── verify.php          # Verifikasi OTP/email untuk user
├── vote.php            # Halaman untuk melakukan voting
└── PHPMailer/          # Library untuk kirim email
    ├── Exception.php
    ├── PHPMailer.php
    └── SMTP.php

# 🗳️ Voting Ketua

## 📌 Deskripsi Proyek
**Voting Ketua** adalah aplikasi web sederhana berbasis **PHP & MySQL** yang digunakan untuk melakukan proses pemilihan ketua secara online.  
Proyek ini dirancang agar mudah digunakan di lingkungan sekolah, organisasi, atau komunitas yang membutuhkan sistem voting yang cepat, transparan, dan terkomputerisasi.  

Aplikasi ini dilengkapi dengan sistem **registrasi akun dengan verifikasi email**, **autentikasi login**, serta **perhitungan hasil voting otomatis** yang dapat diakses langsung oleh pengguna.  

---

## 🚀 Fitur Utama
1. **Manajemen Pengguna**
   - Registrasi akun baru.
   - Verifikasi akun via **email OTP** menggunakan PHPMailer.
   - Login & Logout dengan session.

2. **Voting Online**
   - Pemilih hanya bisa memilih satu kandidat.
   - Voting otomatis tercatat ke database.
   - Mencegah voting ganda (satu akun hanya bisa memilih sekali).

3. **Hasil Voting**
   - Hasil perhitungan voting ditampilkan secara real-time.
   - Tampilan ringkas untuk mengetahui kandidat dengan suara terbanyak.

4. **Keamanan**
   - Autentikasi login sebelum bisa voting.
   - Verifikasi akun dengan email sebelum bisa ikut memilih.

5. **Teknologi yang Digunakan**
   - **Backend**: PHP Native  
   - **Database**: MySQL/MariaDB  
   - **Library**: PHPMailer (untuk OTP/Email)  

---

## ⚙️ Instalasi
1. Clone repository atau ekstrak file ZIP ke folder web server (misalnya `htdocs` di XAMPP atau `var/www/html` di Linux).

2. Import database MySQL:
   ```bash
   mysql -u root -p nama_database < database.sql
   ```

3. Edit file **koneksi.php** dan sesuaikan dengan konfigurasi database Anda:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "voting_ketua";
   ```

4. Konfigurasi PHPMailer di file **mailer.php** agar bisa mengirim OTP ke email.

5. Akses aplikasi melalui browser:
   ```
   http://localhost/voting_ketua
   ```

---

## 👨‍💻 Developer
Dibuat untuk kebutuhan pembelajaran dan implementasi sistem **Voting Online** berbasis web.  
