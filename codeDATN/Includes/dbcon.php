<?php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "attendancemsystem01";

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Failed To Connect Database: " . $conn->connect_error);
    }

    // Hỗ trợ tiếng Việt
    $conn->set_charset("utf8mb4");
?>