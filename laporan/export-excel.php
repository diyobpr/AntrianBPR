<?php
session_start();
require_once "../config/database.php"; // Koneksi ke database
require_once "../vendor/autoload.php"; // Autoload PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Pastikan user sudah login
if (!isset($_SESSION['role_id']) || !isset($_SESSION['cabang_id'])) {
    header("Location: ../login.php");
    exit;
}

$role_id = $_SESSION['role_id'];
$cabang_id = $_SESSION['cabang_id'];

// Inisialisasi variabel untuk filter
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : null;
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : null;
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : null;
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : null;
$filter_cabang = isset($_GET['cabang_id']) ? $_GET['cabang_id'] : ($role_id != 1 ? $cabang_id : null);

// Query dasar untuk mendapatkan data antrian
$query = "SELECT * FROM tbl_antrian WHERE status = '1'";

// Tambahkan filter cabang jika role_id bukan 1 atau jika superadmin menggunakan filter cabang
if (!empty($filter_cabang)) {
    $query .= " AND cabang_id = ?";
}

// Tambahkan filter tanggal awal dan akhir
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $query .= " AND tanggal BETWEEN ? AND ?";
}

// Tambahkan filter bulan
if (!empty($bulan)) {
    $query .= " AND MONTH(tanggal) = ?";
}

// Tambahkan filter tahun
if (!empty($tahun)) {
    $query .= " AND YEAR(tanggal) = ?";
}

$query .= " ORDER BY tanggal ASC, no_antrian ASC";

$stmt = $mysqli->prepare($query);

// Bind parameter ke query
$bind_types = '';
$params = [];
if (!empty($filter_cabang)) {
    $bind_types .= 'i';
    $params[] = $filter_cabang;
}
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $bind_types .= 'ss';
    $params[] = $tanggal_awal;
    $params[] = $tanggal_akhir;
}
if (!empty($bulan)) {
    $bind_types .= 'i';
    $params[] = $bulan;
}
if (!empty($tahun)) {
    $bind_types .= 'i';
    $params[] = $tahun;
}

if (!empty($params)) {
    $stmt->bind_param($bind_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Buat Spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set header kolom
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'Cabang ID');
$sheet->setCellValue('C1', 'Tanggal');
$sheet->setCellValue('D1', 'No Antrian');
$sheet->setCellValue('E1', 'Status');
$sheet->setCellValue('F1', 'Durasi');

// Isi data ke dalam spreadsheet
$rowIndex = 2;
$nomor = 1;
$previous_date = null;

while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue("A{$rowIndex}", $nomor);
    $sheet->setCellValue("B{$rowIndex}", $row['cabang_id']);
    $sheet->setCellValue("C{$rowIndex}", date('d/m/Y', strtotime($row['tanggal'])));
    $sheet->setCellValue("D{$rowIndex}", $row['no_antrian']);
    $sheet->setCellValue("E{$rowIndex}", ($row['status'] == '1' ? 'Selesai' : 'Menunggu'));

    // Hitung durasi
    if ($previous_date) {
        $current_date = strtotime($row['updated_date']);
        $duration = $current_date - $previous_date;
        $formatted_duration = sprintf("%02d:%02d:%02d", floor($duration / 3600), floor(($duration % 3600) / 60), $duration % 60);
    } else {
        $formatted_duration = "-";
    }

    $sheet->setCellValue("F{$rowIndex}", $formatted_duration);

    $previous_date = strtotime($row['updated_date']);
    $rowIndex++;
    $nomor++;
}

// Atur header untuk download file
$filename = "laporan_customer_service.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Cache-Control: max-age=0');

// Tulis file Excel dan kirimkan ke browser
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
