<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Lấy ID của giáo viên từ session
$teacherId = $_SESSION['userId'];

// Truy vấn để lấy danh sách các lớp mà giáo viên được phân công
$query = "SELECT tblclass.className, tblclassarms.classArmName, tblclass.duration, tblclassassignment.classId, tblclassassignment.classArmId
          FROM tblclassassignment 
          INNER JOIN tblclass ON tblclassassignment.classId = tblclass.Id 
          INNER JOIN tblclassarms ON tblclassassignment.classArmId = tblclassarms.Id 
          WHERE tblclassassignment.teacherId = '$teacherId'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Danh sách các lớp</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/admin.min.css" rel="stylesheet">
  <style>
    .class-list {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
    }
    .class-card {
      border: 1px solid #ccc;
      padding: 16px;
      border-radius: 8px;
      text-align: center;
      width: 200px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      cursor: pointer;
    }
  </style>
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

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Danh sách các lớp</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Danh sách các lớp</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Danh sách các lớp</h6>
                </div>
                <div class="card-body">
                  <div class="class-list">
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                      while ($row = mysqli_fetch_assoc($result)) {
                        echo "
                        <a href='takeAttendance.php?classId={$row['classId']}&classArmId={$row['classArmId']}'>
                          <div class='class-card'>
                            <h2>{$row['classArmName']}</h2>
                            <p>Môn: {$row['className']}</p>
                            <p>Thời lượng: {$row['duration']}</p>
                          </div>
                        </a>";
                      }
                    } else {
                      echo "<p>Không có lớp nào được phân công cho giáo viên này.</p>";
                    }
                    ?>
                  </div>
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
