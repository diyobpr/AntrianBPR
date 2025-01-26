<?php
// pengecekan ajax request untuk mencegah direct access file, agar file tidak bisa diakses secara langsung dari browser
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
  // panggil file "database.php" untuk koneksi ke database
  require_once "../config/database.php";

  // mulai session untuk mengambil data cabang pengguna
  session_start();

  // ambil cabang_id dari session
  $cabang_id = $_SESSION['cabang_id'] ?? null;

  // cek apakah cabang_id tersedia
  if (!$cabang_id) {
    die('Akses tidak diizinkan!');
  }

  // mengecek data post dari ajax
  if (isset($_POST['id'])) {
    // ambil data hasil post dari ajax
    $id = mysqli_real_escape_string($mysqli, $_POST['id']);
    // tentukan nilai status
    $status = "1";
    // ambil tanggal dan waktu update data
    $updated_date = gmdate("Y-m-d H:i:s", time() + 60 * 60 * 7);

    // sql statement untuk memastikan bahwa ID yang ingin diperbarui milik cabang pengguna yang login
    $check_query = mysqli_query($mysqli, "SELECT id FROM tbl_antrian 
                                              WHERE id='$id' AND cabang_id='$cabang_id'")
      or die('Ada kesalahan pada query validasi cabang : ' . mysqli_error($mysqli));

    // cek apakah data ditemukan
    if (mysqli_num_rows($check_query) > 0) {
      // sql statement untuk update data di tabel "tbl_antrian" berdasarkan "id"
      $update = mysqli_query($mysqli, "UPDATE tbl_antrian
                                             SET status='$status', updated_date='$updated_date'
                                             WHERE id='$id' AND cabang_id='$cabang_id'")
        or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
    } else {
      // jika data tidak ditemukan, tampilkan pesan error
      die('Data tidak ditemukan atau Anda tidak memiliki akses untuk memperbarui data ini.');
    }
  }
}
