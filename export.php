<?php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) exit();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="data_voting_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Username', 'Email', 'Verified', 'Has Voted', 'Tanggal Daftar']);

$result = $conn->query("SELECT * FROM users");
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
?>