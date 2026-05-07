<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';

$classId = isset($_GET['classId']) ? $_GET['classId'] : '';
$classArmId = isset($_GET['classArmId']) ? $_GET['classArmId'] : '';

if (!$classId || !$classArmId) {
  echo "Lớp học không tồn tại.";
  exit();
}

// Lấy tên lớp từ classArmId
$classArmQuery = "SELECT classArmName FROM tblclassarms WHERE Id='$classArmId'";
$classArmResult = mysqli_query($conn, $classArmQuery);
$classArmRow = mysqli_fetch_assoc($classArmResult);
$classArmName = $classArmRow['classArmName'];

// Lấy danh sách học sinh theo lớp
$query = "SELECT Id, firstName, lastName, admissionNumber FROM tblstudents WHERE classId='$classId' AND classArmId='$classArmId'";
$result = mysqli_query($conn, $query);

if (!$result) {
  echo "Lỗi truy vấn: " . mysqli_error($conn);
  exit();
}

// Xử lý form điểm danh
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  foreach ($_POST['status'] as $studentId => $status) {
    $attendanceDate = date('Y-m-d');
    $query = "INSERT INTO tblattendance (admissionNo, classId, classArmId, status, dateTimeTaken) VALUES ('$studentId', '$classId', '$classArmId', '$status', '$attendanceDate')
              ON DUPLICATE KEY UPDATE status='$status', dateTimeTaken='$attendanceDate'";
    mysqli_query($conn, $query);
  }
  echo "<div class='alert alert-success'>Điểm danh thành công!</div>";
}

// Hàm tính chuyên cần
function calculateDiligence($studentId, $classId, $classArmId, $conn) {
  $query = "SELECT status FROM tblattendance WHERE admissionNo='$studentId' AND classId='$classId' AND classArmId='$classArmId'";
  $result = mysqli_query($conn, $query);
  $totalDays = mysqli_num_rows($result);
  if ($totalDays == 0) return "0%";

  $presentCount = 0;
  $lateCount = 0;
  $absentCount = 0;
  $excusedCount = 0;

  while ($row = mysqli_fetch_assoc($result)) {
    switch ($row['status']) {
      case 'present':
        $presentCount++;
        break;
      case 'late':
        $lateCount++;
        break;
      case 'absent':
        $absentCount++;
        break;
      case 'excused':
        $excusedCount++;
        break;
    }
  }

  // Tính chuyên cần
  $excusedEffective = floor($excusedCount / 2);
  $lateEffective = $lateCount * 0.5; 
  $totalEffectiveAbsent = $absentCount + $lateEffective;

  $effectivePresentDays = $totalDays - $totalEffectiveAbsent + $excusedEffective;
  $diligence = ($effectivePresentDays / $totalDays) * 100;

  // Giới hạn tối đa là 100%
  // if ($diligence > 100) {
  //   $diligence = 100;
  // }

  return number_format($diligence, 2) . "%";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Trang điểm danh</title>
  <link href="img/logo/attnlg.jpg" rel="icon">
  <?php include 'includes/title.php'; ?>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/admin.min.css" rel="stylesheet">
</head>
<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
    <?php include "Includes/sidebar.php"; ?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
        <?php include "Includes/topbar.php"; ?>
        <!-- Topbar -->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Trang điểm danh (Ngày: <?php echo date('Y-m-d'); ?>)</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Trang điểm danh</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Danh sách sinh viên lớp (<?php echo $classArmName; ?>)</h6>
                </div>
                <div class="card-body">
                  <form method="POST">
                    <div class="table-responsive p-3">
                      <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                        <thead class="thead-light">
                          <tr>
                            <th>#</th>
                            <th>Tên học sinh</th>
                            <th>Số ID</th>
                            <th>Ngày điểm danh</th>
                            <th>Chuyên cần</th>
                            <th>Trạng thái</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $sn = 0;
                          while ($row = mysqli_fetch_assoc($result)) {
                            $sn++;
                            $fullName = $row['lastName'] . ' ' . $row['firstName'];
                            $diligence = calculateDiligence($row['admissionNumber'], $classId, $classArmId, $conn);
                            echo "
                            <tr>
                              <td>" . $sn . "</td>
                              <td>" . $fullName . "</td>
                              <td>" . $row['admissionNumber'] . "</td>
                              <td>" . date('Y-m-d') . "</td>
                              <td>" . $diligence . "</td>
                              <td>
                                <input type='radio' name='status[" . $row['admissionNumber'] . "]' value='present'> Có
                                <input type='radio' name='status[" . $row['admissionNumber'] . "]' value='late'> Muộn
                                <input type='radio' name='status[" . $row['admissionNumber'] . "]' value='absent'> Nghỉ
                                <input type='radio' name='status[" . $row['admissionNumber'] . "]' value='excused'> Có phép
                              </td>
                            </tr>";
                          }
                          ?>
                        </tbody>
                      </table>
                    </div>
                    <button type="submit" class="btn btn-primary">Điểm danh</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <!---Container Fluid-->
        </div>
      </div>
      <!-- Footer -->
      <?php include "Includes/footer.php"; ?>
      <!-- Footer -->
    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/admin.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#dataTable').DataTable();
      $('#dataTableHover').DataTable();
    });
  </script>
</body>
</html>
