<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$statusMsg = "";

// Thêm lớp học
if (isset($_POST['save'])) {
  $className = $_POST['className'];
  $duration = $_POST['duration'];

  $query = mysqli_query($conn, "SELECT * FROM tblclass WHERE className ='$className'");
  $ret = mysqli_fetch_array($query);

  if ($ret > 0) {
    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>This Class Already Exists!</div>";
  } else {
    $query = mysqli_query($conn, "INSERT INTO tblclass(className, duration) VALUES('$className', '$duration')");
    if ($query) {
      $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Class Added Successfully!</div>";
    } else {
      $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>";
    }
  }
}

// Sửa lớp học
if (isset($_POST['update'])) {
  $classId = $_POST['classId'];
  $className = $_POST['className'];
  $duration = $_POST['duration'];

  $query = mysqli_query($conn, "UPDATE tblclass SET className='$className', duration='$duration' WHERE Id='$classId'");
  if ($query) {
    $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Class Updated Successfully!</div>";
  } else {
    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>";
  }
}

// Xóa lớp học
if (isset($_GET['action']) && $_GET['action'] == "delete") {
  $classId = $_GET['Id'];

  $query = mysqli_query($conn, "DELETE FROM tblclass WHERE Id='$classId'");
  if ($query) {
    $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Class Deleted Successfully!</div>";
  } else {
    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>";
  }
}

// Lấy thông tin lớp học để sửa
if (isset($_GET['action']) && $_GET['action'] == "edit") {
  $classId = $_GET['Id'];
  $query = mysqli_query($conn, "SELECT * FROM tblclass WHERE Id='$classId'");
  $row = mysqli_fetch_assoc($query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Quản lý môn học</title>
  <link href="img/logo/TL.png" rel="icon">
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
            <h1 class="h3 mb-0 text-gray-800">Trang môn học</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Trang môn học</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <!-- Form Basic -->
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Thêm môn học</h6>
                  <?php echo $statusMsg; ?>
                </div>
                <div class="card-body">
                  <form method="post">
                    <input type="hidden" name="classId" value="<?php echo isset($row['Id']) ? $row['Id'] : ''; ?>">
                    <div class="form-group row mb-3">
                      <div class="col-xl-6">
                        <label class="form-control-label">Tên môn học<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control" required name="className" value="<?php echo isset($row['className']) ? $row['className'] : ''; ?>" id="exampleInputFirstName">
                      </div>
                      <div class="col-xl-6">
                        <label class="form-control-label">Thời lượng<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control" required name="duration" value="<?php echo isset($row['duration']) ? $row['duration'] : ''; ?>" id="exampleInputDuration">
                      </div>
                    </div>
                    <?php if (isset($_GET['action']) && $_GET['action'] == "edit") { ?>
                      <button type="submit" name="update" class="btn btn-primary">Cập nhật</button>
                    <?php } else { ?>
                      <button type="submit" name="save" class="btn btn-primary">Thêm</button>
                    <?php } ?>
                  </form>
                </div>
              </div>

              <!-- Input Group -->
              <div class="row">
                <div class="col-lg-12">
                  <div class="card mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                      <h6 class="m-0 font-weight-bold text-primary">Danh sách môn học</h6>
                    </div>
                    <div class="table-responsive p-3">
                      <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                        <thead class="thead-light">
                          <tr>
                            <th>#</th>
                            <th>Tên môn</th>
                            <th>Thời lượng</th>
                            <th>Sửa</th>
                            <th>Xóa</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $query = "SELECT * FROM tblclass";
                          $rs = $conn->query($query);
                          $num = $rs->num_rows;
                          $sn = 0;
                          if ($num > 0) {
                            while ($rows = $rs->fetch_assoc()) {
                              $sn++;
                              echo "
                              <tr>
                                <td>" . $sn . "</td>
                                <td>" . $rows['className'] . "</td>
                                <td>" . $rows['duration'] . "</td>
                                <td><a href='createClass.php?action=edit&Id=" . $rows['Id'] . "'><i class='fas fa-fw fa-edit'></i>Sửa</a></td>
                                <td><a href='createClass.php?action=delete&Id=" . $rows['Id'] . "'><i class='fas fa-fw fa-trash'></i>Xóa</a></td>
                              </tr>";
                            }
                          } else {
                            echo "<tr><td colspan='5'>Không có môn học nào được tìm thấy.</td></tr>";
                          }
                          ?>
                        </tbody>
                      </table>
                    </div>
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
