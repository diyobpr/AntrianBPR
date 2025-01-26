<?php
// Pengecekan ajax request untuk mencegah direct access file
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
    // Memulai sesi untuk mengambil data pengguna yang login
    session_start();

    // Panggil file "database.php" untuk koneksi ke database
    require_once "../config/database.php";

    // Cek apakah pengguna sudah login (untuk memastikan session tersedia)
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['cabang_id'])) {
        die('Akses tidak diizinkan!');
    }

    // Ambil cabang_id dari session
    $cabang_id = $_SESSION['cabang_id'];

    // Ambil tanggal sekarang
    $tanggal = gmdate("Y-m-d", time() + 60 * 60 * 7);

    // Membuat "no_antrian_teller"
    // SQL statement untuk menampilkan data "no_antrian_teller" terakhir pada tabel "tbl_antrian_teller" berdasarkan "tanggal" dan "cabang_id"
    $query = mysqli_query($mysqli, "SELECT MAX(no_antrian_teller) as nomor FROM tbl_antrian_teller WHERE tanggal_teller='$tanggal' AND cabang_id='$cabang_id'")
        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));

    // Ambil data hasil query
    $data = mysqli_fetch_assoc($query);

    // Jika "no_antrian_teller" sudah ada, tambahkan 1. Jika belum, mulai dari 1.
    $no_antrian = isset($data['nomor']) ? $data['nomor'] + 1 : 1;

    // Pastikan nomor belum ada di database sebelum melakukan insert
    $checkQuery = mysqli_query($mysqli, "SELECT COUNT(*) AS count FROM tbl_antrian_teller WHERE tanggal_teller='$tanggal' AND cabang_id='$cabang_id' AND no_antrian_teller='$no_antrian'")
        or die('Ada kesalahan pada query pengecekan data : ' . mysqli_error($mysqli));

    $checkResult = mysqli_fetch_assoc($checkQuery);

    if ($checkResult['count'] == 0) {
        // Jika nomor belum ada, lakukan insert
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_antrian_teller(tanggal_teller, no_antrian_teller, cabang_id, bagian) 
                                         VALUES('$tanggal', '$no_antrian', '$cabang_id', NULL)") // bagian = NULL
            or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));

        // Cek query
        if ($insert) {
            echo "Sukses";
        }
    } else {
        // Jika nomor sudah ada, tampilkan pesan gagal
        echo "Nomor sudah ada";
    }
}
