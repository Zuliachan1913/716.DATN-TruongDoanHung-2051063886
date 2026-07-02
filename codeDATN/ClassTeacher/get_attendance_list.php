<?php
header('Content-Type: application/json; charset=utf-8');
include '../Includes/dbcon.php';

$classId = isset($_GET['classId']) ? intval($_GET['classId']) : 0;
$classArmId = isset($_GET['classArmId']) ? intval($_GET['classArmId']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$status = isset($_GET['status']) ? $_GET['status'] : 'present';

$result = [];

if ($classId <= 0) {
    echo json_encode(['error' => 'Invalid classId']);
    exit;
}

if (in_array($status, ['present','late','excused'])) {
    if ($classId > 0) {
        $query = "SELECT s.firstName, s.lastName, s.admissionNumber, a.status
            FROM tblstudents s
            JOIN tblattendance a ON s.admissionNumber = a.admissionNo AND a.dateTimeTaken = '". $conn->real_escape_string($date) ."' AND a.classId = '". $conn->real_escape_string($classId) ."' AND a.classArmId = '". $conn->real_escape_string($classArmId) ."' 
            WHERE s.classId = '". $conn->real_escape_string($classId) ."' AND s.classArmId = '". $conn->real_escape_string($classArmId) ."' AND a.status = '". $conn->real_escape_string($status) ."'";
    } else {
        // all classes
        $query = "SELECT s.firstName, s.lastName, s.admissionNumber, a.status
            FROM tblstudents s
            JOIN tblattendance a ON s.admissionNumber = a.admissionNo AND a.dateTimeTaken = '". $conn->real_escape_string($date) ."' 
            WHERE a.status = '". $conn->real_escape_string($status) ."'";
    }
    $res = $conn->query($query);
    while ($row = $res->fetch_assoc()) {
        $result[] = $row;
    }
} elseif ($status === 'absent') {
    // students with no attendance record for the date OR with status 'absent'
    if ($classId > 0) {
        $query = "SELECT s.firstName, s.lastName, s.admissionNumber, IFNULL(a.status,'absent') as status
            FROM tblstudents s
            LEFT JOIN tblattendance a ON s.admissionNumber = a.admissionNo AND a.dateTimeTaken = '". $conn->real_escape_string($date) ."' AND a.classId = '". $conn->real_escape_string($classId) ."' AND a.classArmId = '". $conn->real_escape_string($classArmId) ."'
            WHERE s.classId = '". $conn->real_escape_string($classId) ."' AND s.classArmId = '". $conn->real_escape_string($classArmId) ."' AND (a.Id IS NULL OR a.status = 'absent')";
    } else {
        $query = "SELECT s.firstName, s.lastName, s.admissionNumber, IFNULL(a.status,'absent') as status
            FROM tblstudents s
            LEFT JOIN tblattendance a ON s.admissionNumber = a.admissionNo AND a.dateTimeTaken = '". $conn->real_escape_string($date) ."' 
            WHERE (a.Id IS NULL OR a.status = 'absent')";
    }
    $res = $conn->query($query);
    while ($row = $res->fetch_assoc()) {
        $result[] = $row;
    }
} else {
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

echo json_encode(['data' => $result]);
