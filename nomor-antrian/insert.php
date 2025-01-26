<?php
// pengecekan ajax request untuk mencegah direct access file, agar file tidak bisa diakses secara langsung dari browser
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
    // Memulai sesi untuk mengambil data pengguna yang login
    session_start();

    // panggil file "database.php" untuk koneksi ke database
    require_once "../config/database.php";

    // Cek apakah pengguna sudah login (untuk memastikan session tersedia)
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['cabang_id'])) {
        die('Akses tidak diizinkan!');
    }

    // Ambil cabang_id dari session
    $cabang_id = $_SESSION['cabang_id'];

    // ambil tanggal sekarang
    $tanggal = gmdate("Y-m-d", time() + 60 * 60 * 7);

    // membuat "no_antrian"
    // sql statement untuk menampilkan data "no_antrian" terakhir pada tabel "tbl_antrian" berdasarkan "tanggal" dan "cabang_id"
    $query = mysqli_query($mysqli, "SELECT MAX(no_antrian) as nomor FROM tbl_antrian WHERE tanggal='$tanggal' AND cabang_id='$cabang_id'")
        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));

    // ambil data hasil query
    $data = mysqli_fetch_assoc($query);

    // "no_antrian" = "no_antrian" yang terakhir + 1
    $no_antrian = isset($data['nomor']) ? $data['nomor'] + 1 : 1;

    // Pastikan nomor antrian belum ada sebelum melakukan insert
    $checkQuery = mysqli_query($mysqli, "SELECT COUNT(*) AS count FROM tbl_antrian WHERE tanggal='$tanggal' AND cabang_id='$cabang_id' AND no_antrian='$no_antrian'")
        or die('Ada kesalahan pada query pengecekan data : ' . mysqli_error($mysqli));

    $checkResult = mysqli_fetch_assoc($checkQuery);

    // Jika nomor antrian belum ada, lakukan insert
    if ($checkResult['count'] == 0) {
        // sql statement untuk insert data ke tabel "tbl_antrian"
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_antrian(tanggal, no_antrian, cabang_id) 
                                         VALUES('$tanggal', '$no_antrian', '$cabang_id')")
            or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));

        // cek query
        if ($insert) {
            // tampilkan pesan sukses insert data
            echo "Sukses";
        }
    } else {
        // Jika nomor sudah ada, tampilkan pesan gagal
        echo "Nomor sudah ada";
    }
}
