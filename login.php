<?php
session_start(); // Memulai sesi
include "config/database.php"; // Koneksi ke database

// Ambil data role dari tabel role
$roles = [];
$roleQuery = "SELECT role_id, nama FROM role";
$roleResult = $mysqli->query($roleQuery);
if ($roleResult->num_rows > 0) {
    while ($row = $roleResult->fetch_assoc()) {
        $roles[] = $row;
    }
}

// Ambil data cabang dari tabel cabang
$cabangs = [];
$cabangQuery = "SELECT id, nama FROM cabang";
$cabangResult = $mysqli->query($cabangQuery);
if ($cabangResult->num_rows > 0) {
    while ($row = $cabangResult->fetch_assoc()) {
        $cabangs[] = $row;
    }
}

// Cek jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role_id = $_POST['role_id']; // Role yang dipilih
    $cabang_id = $_POST['cabang_id']; // Cabang yang dipilih

    // Mencari username di database dengan role dan cabang yang sesuai
    $query = "SELECT * FROM users WHERE username = ? AND role_id = ? AND cabang_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sii", $username, $role_id, $cabang_id); // "sii" artinya string, int, int
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Login berhasil, set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['cabang_id'] = $user['cabang_id'];

            // Redirect ke halaman index.php
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username, Role, atau Cabang tidak sesuai!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BPR Sukabumi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css"> <!-- Tambahkan file CSS jika ada -->
</head>

<body class="d-flex flex-column h-100">
    <main class="flex-shrink-0">
        <div class="container pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <!-- Tampilkan pesan error jika ada -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <!-- Card Form Login -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-5">
                            <h3 class="card-title text-center mb-4">Login</h3>
                            <form method="POST" action="login.php">
                                <!-- Input Username -->
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" name="username" id="username" class="form-control" required>
                                </div>

                                <!-- Input Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                </div>

                                <!-- Dropdown Role -->
                                <div class="mb-3">
                                    <label for="role_id" class="form-label">Role</label>
                                    <select name="role_id" id="role_id" class="form-select" required>
                                        <option value="" disabled selected>Pilih Role</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role['role_id']; ?>">
                                                <?php echo htmlspecialchars($role['nama']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Dropdown Cabang -->
                                <div class="mb-3">
                                    <label for="cabang_id" class="form-label">Cabang</label>
                                    <select name="cabang_id" id="cabang_id" class="form-select" required>
                                        <option value="" disabled selected>Pilih Cabang</option>
                                        <?php foreach ($cabangs as $cabang): ?>
                                            <option value="<?php echo $cabang['id']; ?>">
                                                <?php echo htmlspecialchars($cabang['nama']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Tombol Login -->
                                <button type="submit" class="btn btn-success w-100 py-2">Login</button>
                            </form>

                        </div>
                    </div>
                    <?php include "footer.php"; ?> <!-- Menyertakan file footer.php -->

                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>