<?php
require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role_id = $_POST['role_id'];
    $cabang_id = $_POST['cabang_id'];

    // Cek apakah username sudah ada
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Username sudah ada, kembalikan pesan error
        http_response_code(400); // Set response HTTP 400 (Bad Request)
        echo json_encode(['error' => 'Username sudah digunakan']);
        exit;
    } else {
        // Tambahkan user baru
        $insertQuery = "INSERT INTO users (username, password, role_id, cabang_id) VALUES (?, ?, ?, ?)";
        $insertStmt = $mysqli->prepare($insertQuery);
        $insertStmt->bind_param("ssii", $username, $password, $role_id, $cabang_id);

        if ($insertStmt->execute()) {
            // Berhasil
            echo json_encode(['success' => true]);
        } else {
            // Gagal
            http_response_code(500); // Set response HTTP 500 (Internal Server Error)
            echo json_encode(['error' => 'Gagal menambahkan user']);
        }
    }
}
