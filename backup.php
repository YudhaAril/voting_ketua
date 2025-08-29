<?php
session_start();
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) exit();

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'nama_database'; // ganti dengan nama DB kamu

$backupFile = "backup_db_" . date("Y-m-d_H-i-s") . ".sql";
exec("mysqldump --host=$host --user=$user --password=$pass $db > $backupFile");

if (file_exists($backupFile)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($backupFile));
    readfile($backupFile);
    unlink($backupFile);
    exit();
}
?>