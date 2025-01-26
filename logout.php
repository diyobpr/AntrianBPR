<?php
session_start();
session_destroy(); // Menghapus semua session
header("Location: login.php"); // Redirect ke halaman login
exit;
