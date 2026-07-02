<?php
error_reporting(E_ALL); // Hiển thị tất cả các lỗi
ini_set('display_errors', 1); // Bật hiển thị lỗi
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Khởi tạo biến
$statusMsg = "";
$row = [];

//------------------------SAVE--------------------------------------------------

if (isset($_POST['save'])) {

  $firstName = $_POST['firstName'];
  $lastName = $_POST['lastName'];
  $emailAddress = $_POST['emailAddress'];
  $phoneNo = $_POST['phoneNo'];
  $dateCreated = date("Y-m-d");

  $query = mysqli_query($conn, "select * from tblclassteacher where emailAddress ='$emailAddress'");
  $ret = mysqli_fetch_array($query);

  $sampPass = "pass123";
  $sampPass_2 = md5($sampPass);

  if ($ret > 0) {
    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>This Email Address Already Exists!</div>";
  } else {
    $queryString = "INSERT into tblclassteacher(firstName,lastName,emailAddress,password,phoneNo,dateCreated) 
    value('$firstName','$lastName','$emailAddress','$sampPass_2','$phoneNo','$dateCreated')";

    $query = mysqli_query($conn, $queryString);

    if ($query) {
      $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Created Successfully!</div>";
    } else {
      $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>";
    }
  }
}

//--------------------EDIT------------------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "edit") {
  $Id = $_GET['Id'];
  $query = mysqli_query($conn, "select * from tblclassteacher where Id ='$Id'");
  $row = mysqli_fetch_array($query);

  if (isset($_POST['update'])) {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $emailAddress = $_POST['emailAddress'];
    $phoneNo = $_POST['phoneNo'];
    $dateCreated = date("Y-m-d");

    $queryString = "update tblclassteacher set firstName='$firstName', lastName='$lastName',
    emailAddress='$emailAddress', phoneNo='$phoneNo', dateCreated='$dateCreated' where Id='$Id'";

    $query = mysqli_query($conn, $queryString);

    if ($query) {
      echo "<script type = \"text/javascript\">
              window.location = (\"createClassTeacher.php\")
            </script>";
    } else {
      $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>";
    }
  }
}

//--------------------------------DELETE------------------------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete") {
  $Id = $_GET['Id'];
  $query = mysqli_query($conn, "DELETE FROM tblclassteacher WHERE Id='$Id'");

  if ($query == TRUE) {
    echo "<script type = \"text/javascript\">
            window.location = (\"createClassTeacher.php\")
          </script>";
  } else {
    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>";
  }
}

if (isset($_POST['assignClass'])) {
  $teacherId = $_POST['teacherId'];
  $classId = $_POST['classId'];
  $classArmId = $_POST['classArmId'];

  $queryString = "INSERT into tblclassassignment(teacherId,classId,classArmId) values('$teacherId','$classId','$classArmId')";

  $query = mysqli_query($conn, $queryString);

  if ($query) {
    $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Class Assigned Successfully!</div>";
  } else {
    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
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
            <h1 class="h3 mb-0 text-gray-800">Trang giáo viên</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Trang giáo viên</li>
            </ol>
          </div>
          <div class="row">
            <div class="col-lg-12">
              <!-- Form Basic -->
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Thêm giáo viên mới</h6>
                  <?php echo $statusMsg; ?>
                </div>
                <div class="card-body">
                  <form method="post">
                    <div class="form-group row mb-3">
                      <div class="col-xl-6">
                        <label class="form-control-label">Họ<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control" required name="lastName"
                          value="<?php echo isset($row['lastName']) ? $row['lastName'] : ''; ?>" id="exampleInputFirstName">
                      </div>
                      <div class="col-xl-6">
                        <label class="form-control-label">Tên<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control" required name="firstName"
                          value="<?php echo isset($row['firstName']) ? $row['firstName'] : ''; ?>" id="exampleInputFirstName">
                      </div>
                    </div>
                    <div class="form-group row mb-3">
                      <div class="col-xl-6">
                        <label class="form-control-label">Địa chỉ email<span class="text-danger ml-2">*</span></label>
                        <input type="email" class="form-control" required name="emailAddress"
                          value="<?php echo isset($row['emailAddress']) ? $row['emailAddress'] : ''; ?>" id="exampleInputFirstName">
                      </div>
                      <div class="col-xl-6">
                        <label class="form-control-label">SDT<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control" name="phoneNo" value="<?php echo isset($row['phoneNo']) ? $row['phoneNo'] : ''; ?>"
                          id="exampleInputFirstName">
                      </div>
                    </div>
                    <?php
                    if (isset($Id)) {
                      ?>
                      <button type="submit" name="update" class="btn btn-warning">Cập nhật</button>
                      <?php
                    } else {
                      ?>
                      <button type="submit" name="save" class="btn btn-primary">Thêm</button>
                      <?php
                    }
                    ?>
                  </form>
                </div>
              </div>

              <!-- Input Group -->
              <div class="row">
                <div class="col-lg-12">
                  <div class="card mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                      <h6 class="m-0 font-weight-bold text-primary">Danh sách</h6>
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#assignClassModal">
                       Thêm lớp học 
                      </button>
                    </div>
                    <div class="table-responsive p-3">
                      <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                        <thead class="thead-light">
                          <tr>
                            <th>#</th>
                            <th>Họ</th>
                            <th>Tên</th>
                            <th>Địa chỉ email</th>
                            <th>SDT</th>
                            <th>Ngày tạo</th>
                            <th>Sửa</th>
                            <th>Xóa</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $query = "SELECT Id, firstName, lastName, emailAddress, phoneNo, dateCreated FROM tblclassteacher";
                          $rs = $conn->query($query);
                          $num = $rs->num_rows;
                          $sn = 0;
                          if ($num > 0) {
                            while ($rows = $rs->fetch_assoc()) {
                              $sn++;
                              echo "
                              <tr>
                                <td>" . $sn . "</td>
                                <td>" . $rows['lastName'] . "</td>
                                <td>" . $rows['firstName'] . "</td>
                                <td>" . $rows['emailAddress'] . "</td>
                                <td>" . $rows['phoneNo'] . "</td>
                                <td>" . $rows['dateCreated'] . "</td>
                                <td><a href='?action=edit&Id=" . $rows['Id'] . "'><i class='fas fa-fw fa-edit'></i>Sửa</a></td>
                                <td><a href='?action=delete&Id=" . $rows['Id'] . "'><i class='fas fa-fw fa-trash'></i>Xóa</a></td>
                              </tr>";
                            }
                          } else {
                            echo "<div class='alert alert-danger' role='alert'>No Record Found!</div>";
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
        </div>
      </div>
      <!-- Footer -->
      <?php include "Includes/footer.php"; ?>
      <!-- Footer -->
    </div>
    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
      <i class="fas fa-angle-up"></i>
    </a>
    <!-- Assign Class Modal -->
    <div class="modal fade" id="assignClassModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form method="post">
              <div class="form-group">
                <label for="teacherId">Chọn giáo viên</label>
                <select class="form-control" id="teacherId" name="teacherId" required>
                  <option value="">Chọn giáo viên</option>
                  <?php
                  $query = "SELECT Id, firstName, lastName FROM tblclassteacher";
                  $rs = $conn->query($query);
                  while ($rows = $rs->fetch_assoc()) {
                    echo "<option value='" . $rows['Id'] . "'>" . $rows['firstName'] . " " . $rows['lastName'] . "</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="form-group">
                <label for="classId">Chọn lớp học</label>
                <select class="form-control" id="classId" name="classId" required>
                  <option value="">Chọn lớp học</option>
                  <?php
                  $query = "SELECT Id, className FROM tblclass";
                  $rs = $conn->query($query);
                  while ($rows = $rs->fetch_assoc()) {
                    echo "<option value='" . $rows['Id'] . "'>" . $rows['className'] . "</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="form-group">
                <label for="classArmId">Chọn phân nhóm</label>
                <select class="form-control" id="classArmId" name="classArmId" required>
                  <option value="">Chọn phân nhóm</option>
                  <?php
                  $query = "SELECT Id, classArmName FROM tblclassarms";
                  $rs = $conn->query($query);
                  while ($rows = $rs->fetch_assoc()) {
                    echo "<option value='" . $rows['Id'] . "'>" . $rows['classArmName'] . "</option>";
                  }
                  ?>
                </select>
              </div>
              <button type="submit" name="assignClass" class="btn btn-primary">Thêm lớp học</button>
            </form>
          </div>
        </div>
      </div>
    </div>
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
