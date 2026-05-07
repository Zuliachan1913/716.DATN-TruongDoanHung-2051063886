<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

if (isset($_POST['search'])) {
    $admissionNo = $_POST['admissionNo'];

    $query = mysqli_query($conn, "SELECT * FROM tblstudents WHERE admissionNumber='$admissionNo'");
    $row = mysqli_fetch_array($query);

    if ($row) {
        $studentId = $row['Id'];
        $studentName = $row['firstName'] . " " . $row['lastName'];
        
        $attendanceQuery = mysqli_query($conn, "SELECT tblattendance.*, tblclass.className, tblclassarms.classArmName FROM tblattendance 
                                                INNER JOIN tblclass ON tblattendance.classId = tblclass.Id 
                                                INNER JOIN tblclassarms ON tblattendance.classArmId = tblclassarms.Id 
                                                WHERE tblattendance.admissionNo='$admissionNo'");
        
        $attendanceData = [];
        while ($attendanceRow = mysqli_fetch_array($attendanceQuery)) {
            $attendanceData[] = $attendanceRow;
        }
    } else {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>No Student Found with the given Admission Number!</div>";
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
    <link href="img/logo/attnlg.jpg" rel="icon">
    <?php include 'includes/title.php'; ?>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include "Includes/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include "Includes/topbar.php"; ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Xem Điểm Danh Học Sinh</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Xem Điểm Danh Học Sinh</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Tìm Kiếm Học Sinh</h6>
                                    <?php echo $statusMsg; ?>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label for="admissionNo">Số ID</label>
                                            <input type="text" name="admissionNo" class="form-control" id="admissionNo" required>
                                        </div>
                                        <button type="submit" name="search" class="btn btn-primary">Tìm Kiếm</button>
                                    </form>
                                </div>
                            </div>

                            <?php if (isset($attendanceData) && !empty($attendanceData)) { ?>
                                <div class="card mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h6 class="m-0 font-weight-bold text-primary">Danh Sách Điểm Danh của Học Sinh: <?php echo $studentName; ?></h6>
                                    </div>
                                    <div class="table-responsive p-3">
                                        <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Môn</th>
                                                    <th>Lớp</th>
                                                    <th>Ngày Điểm Danh</th>
                                                    <th>Trạng Thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sn = 1;
                                                foreach ($attendanceData as $data) {
                                                    // Convert status to Vietnamese
                                                    switch ($data['status']) {
                                                        case 'present':
                                                            $statusText = 'Có';
                                                            break;
                                                        case 'late':
                                                            $statusText = 'Muộn';
                                                            break;
                                                        case 'absent':
                                                            $statusText = 'Nghỉ';
                                                            break;
                                                        case 'excused':
                                                            $statusText = 'Có phép';
                                                            break;
                                                        default:
                                                            $statusText = $data['status'];
                                                            break;
                                                    }
                                                    echo "<tr>
                                                        <td>{$sn}</td>
                                                        <td>{$data['className']}</td>
                                                        <td>{$data['classArmName']}</td>
                                                        <td>{$data['dateTimeTaken']}</td>
                                                        <td>{$statusText}</td>
                                                    </tr>";
                                                    $sn++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php } else if (isset($studentId)) { ?>
                                <div class="alert alert-info">Không tìm thấy bản ghi điểm danh cho học sinh này.</div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php include "Includes/footer.php"; ?>
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
            $('#dataTableHover').DataTable();
        });
    </script>
</body>

</html>
