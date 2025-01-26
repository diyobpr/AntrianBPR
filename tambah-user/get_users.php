<?php
require_once "../config/database.php";

$query = "
    SELECT users.id, users.username, role.nama AS role, cabang.nama AS cabang, users.role_id, users.cabang_id
    FROM users
    JOIN role ON users.role_id = role.role_id
    JOIN cabang ON users.cabang_id = cabang.id
";
$result = $mysqli->query($query);

$users = [];
if ($result && $result->num_rows > 0) {
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $row['no'] = $no++; // Tambahkan nomor urut
        $users[] = $row;
    }
}

echo json_encode(['data' => $users]);
