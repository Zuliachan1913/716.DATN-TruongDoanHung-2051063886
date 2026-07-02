<?php
session_start();
include('../Includes/dbcon.php');
include('../Includes/session.php');

$classId = $_SESSION['classId'] ?? null;
$classArmId = $_SESSION['classArmId'] ?? null;

// Lấy danh sách các lớp giáo viên dạy
$teacherClasses = $conn->query("
    SELECT tblclassassignment.classId, tblclassassignment.classArmId, 
           tblclass.className, tblclassarms.classArmName 
    FROM tblclassassignment 
    INNER JOIN tblclass ON tblclass.Id = tblclassassignment.classId 
    INNER JOIN tblclassarms ON tblclassarms.Id = tblclassassignment.classArmId 
    WHERE tblclassassignment.teacherId = " . intval($_SESSION['userId'])
)->fetch_all(MYSQLI_ASSOC);

$selectedClassKey = $_GET['selectedClass'] ?? null;
if ($selectedClassKey) {
    list($classId, $classArmId) = array_map('intval', explode('_', $selectedClassKey));
} elseif ((!$classId || !$classArmId) && count($teacherClasses) > 0) {
    $classId = $teacherClasses[0]['classId'];
    $classArmId = $teacherClasses[0]['classArmId'];
}

// Lấy thông tin lớp
$classInfo = $conn->query("SELECT className FROM tblclass WHERE Id = " . intval($classId))->fetch_assoc();
$classArmInfo = $conn->query("SELECT classArmName FROM tblclassarms WHERE Id = " . intval($classArmId))->fetch_assoc();

// Hôm nay
$today = date('Y-m-d');

// Tổng số học sinh trong lớp
 $totalStudents = 0;
if (intval($classId) > 0 && intval($classArmId) > 0) {
    $totalStudents = $conn->query("SELECT COUNT(*) as total FROM tblstudents WHERE classId = " . intval($classId) . " AND classArmId = " . intval($classArmId))->fetch_assoc()['total'];
}

// Điểm danh hôm nay
$attendanceToday = [];
if (intval($classId) > 0 && intval($classArmId) > 0) {
    $attendanceToday = $conn->query("SELECT status, COUNT(*) as count FROM tblattendance WHERE classId = " . intval($classId) . " AND classArmId = " . intval($classArmId) . " AND dateTimeTaken = '$today' GROUP BY status")->fetch_all(MYSQLI_ASSOC);
}

$stats = [
    'present' => 0,
    'late' => 0,
    'absent' => 0,
    'excused' => 0
];

foreach ($attendanceToday as $record) {
    $stats[$record['status']] = $record['count'];
}

// Tỷ lệ điểm danh hôm nay
$attendedToday = $stats['present'] + $stats['late'];
$attendanceRate = $totalStudents > 0 ? round(($attendedToday / $totalStudents) * 100, 2) : 0;

// Thống kê 7 ngày gần đây
$sevenDaysAgo = date('Y-m-d', strtotime('-6 days'));
$dailyAttendance = [];
if (intval($classId) > 0 && intval($classArmId) > 0) {
    $dailyAttendance = $conn->query("
    SELECT dateTimeTaken, 
           SUM(IF(status IN ('present', 'late'), 1, 0)) as attended,
           COUNT(*) as total
    FROM tblattendance 
    WHERE classId = " . intval($classId) . " AND classArmId = " . intval($classArmId) . " AND dateTimeTaken >= '$sevenDaysAgo'
    GROUP BY dateTimeTaken 
    ORDER BY dateTimeTaken ASC
    ")->fetch_all(MYSQLI_ASSOC);
}

// Tổng số ngày trong hệ thống
$totalDays = 0;
if (intval($classId) > 0 && intval($classArmId) > 0) {
    $totalDays = $conn->query("SELECT COUNT(DISTINCT dateTimeTaken) as total 
        FROM tblattendance 
        WHERE classId = " . intval($classId) . " AND classArmId = " . intval($classArmId)
    )->fetch_assoc()['total'];
}


// Cảnh báo chuyên cần - nghỉ không phép từ 3 buổi trở lên
$warnings = [];
if (intval($classId) > 0 && intval($classArmId) > 0) {
    $warnings = $conn->query("
        SELECT s.firstName, s.lastName, s.admissionNumber,
               COUNT(*) as sobuoinghi
        FROM tblattendance a
        INNER JOIN tblstudents s ON s.admissionNumber = a.admissionNo
        WHERE a.classId = " . intval($classId) . " 
          AND a.classArmId = " . intval($classArmId) . "
          AND a.status = 'absent'
        GROUP BY a.admissionNo, s.firstName, s.lastName, s.admissionNumber
        HAVING sobuoinghi >= 2
        ORDER BY sobuoinghi DESC
    ")->fetch_all(MYSQLI_ASSOC);
}
?>



<!DOCTYPE html>
<html lang="vi">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Dashboard Phân Tích - Giáo Viên</title>
    <link href="../img/logo/TL.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/admin.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        .dashboard-container {
            padding: 20px;
        }
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card-header {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .stat-card-value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        .stat-card-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .stat-present { color: #28a745; }
        .stat-late { color: #ffc107; }
        .stat-absent { color: #dc3545; }
        .stat-excused { color: #17a2b8; }
        
        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            height: 350px;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        .page-header {
            margin-bottom: 30px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        .page-subtitle {
            color: #6c757d;
            margin: 5px 0 0 0;
        }
        
        .attendance-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin: 2px;
        }
        
        .badge-present {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-late {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-absent {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-excused {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        table tbody td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        table tbody tr:hover {
            background: #f5f5f5;
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include('Includes/sidebar.php'); ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('Includes/topbar.php'); ?>
                
                <div class="dashboard-container">
                    <div class="page-header">
                        <h1 class="page-title">📊 Dashboard Phân Tích Điểm Danh</h1>
                        <p class="page-subtitle">
                            Lớp: <strong><?php echo htmlspecialchars($classInfo['className'] ?? 'N/A'); ?></strong> - 
                            Tổ: <strong><?php echo htmlspecialchars($classArmInfo['classArmName'] ?? 'N/A'); ?></strong> |
                            Cập nhật: <?php echo date('d/m/Y H:i'); ?>
                        </p>
                        <form method="get" class="mb-3" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <label for="selectedClass" style="margin:0; font-weight:600;">Chọn lớp để phân tích:</label>
                            <select id="selectedClass" name="selectedClass" class="form-control" style="min-width:220px; display:inline-block;" onchange="this.form.submit()">
                                <option value="">-- Chọn lớp --</option>
                                <?php foreach ($teacherClasses as $item):
                                    $value = $item['classId'].'_'.$item['classArmId'];
                                    $selected = ($item['classId'] == $classId && $item['classArmId'] == $classArmId) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($item['className'].' - '.$item['classArmName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    
                    <!-- Thống kê hôm nay -->
                    <h3 style="margin-top: 30px; margin-bottom: 15px;">📅 Thống Kê Hôm Nay (<?php echo date('d/m/Y'); ?>)</h3>
                    <div class="stat-row">
                        <div class="stat-card" data-status="present" data-classid="<?php echo intval($classId); ?>" data-classarmid="<?php echo intval($classArmId); ?>">
                            <div class="stat-card-icon stat-present">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-card-header">Có Mặt</div>
                            <div class="stat-card-value"><?php echo $stats['present']; ?></div>
                        </div>
                        
                        <div class="stat-card" data-status="late" data-classid="<?php echo intval($classId); ?>" data-classarmid="<?php echo intval($classArmId); ?>">
                            <div class="stat-card-icon stat-late">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-card-header">Đi Muộn</div>
                            <div class="stat-card-value"><?php echo $stats['late']; ?></div>
                        </div>
                        
                        <div class="stat-card" data-status="absent" data-classid="<?php echo intval($classId); ?>" data-classarmid="<?php echo intval($classArmId); ?>">
                            <div class="stat-card-icon stat-absent">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-card-header">Vắng Mặt</div>
                            <div class="stat-card-value"><?php echo $stats['absent']; ?></div>
                        </div>
                        
                        <div class="stat-card" data-status="excused" data-classid="<?php echo intval($classId); ?>" data-classarmid="<?php echo intval($classArmId); ?>">
                            <div class="stat-card-icon stat-excused">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="stat-card-header">Có Phép</div>
                            <div class="stat-card-value"><?php echo $stats['excused']; ?></div>
                        </div>
                    </div>
                    
                    <!-- Tỷ lệ điểm danh -->
                    <div class="stat-row">
                        <div class="stat-card" data-action="breakdown" data-classid="<?php echo intval($classId); ?>" data-classarmid="<?php echo intval($classArmId); ?>">
                            <div class="stat-card-icon" style="color: #007bff;">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-card-header">Tỷ Lệ Điểm Danh</div>
                            <div class="stat-card-value"><?php echo $attendanceRate; ?>%</div>
                            <p style="color: #6c757d; margin: 10px 0 0 0; font-size: 14px;">
                                <?php echo $attendedToday; ?>/<?php echo $totalStudents; ?> học sinh
                            </p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-card-icon" style="color: #6f42c1;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-card-header">Tổng Số Học Sinh</div>
                            <div class="stat-card-value"><?php echo $totalStudents; ?></div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-card-icon" style="color: #e83e8c;">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-card-header">Tổng Số Ngày</div>
                            <div class="stat-card-value"><?php echo $totalDays; ?></div>
                        </div>
                    </div>
                    
                    <!-- Biểu đồ điểm danh hôm nay -->
                    <div class="chart-container">
                        <canvas id="todayChart"></canvas>
                    </div>
                    
                    <!-- Biểu đồ 7 ngày -->
                    <div class="chart-container">
                        <canvas id="weekChart"></canvas>

                        <!-- Bảng cảnh báo chuyên cần -->
<?php if (!empty($warnings)): ?>
<div class="table-container" style="margin-top: 20px;">
    <h4 style="margin-bottom: 15px;">
        ⚠️ Cảnh Báo Chuyên Cần
        <span style="font-size:14px; font-weight:normal; color:#6c757d; margin-left:10px;">
            (nghỉ không phép từ 3 buổi trở lên)
        </span>
    </h4>
    <table class="table align-items-center table-flush table-hover">
        <thead class="thead-light">
            <tr>
                <th>#</th>
                <th>Họ và tên</th>
                <th>MSSV</th>
                <th>Số buổi nghỉ</th>
                <th>Mức độ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($warnings as $i => $w): 
                $sobuoi = $w['sobuoinghi'];
                if ($sobuoi >= 4) {
                    $badge = '<span style="background:#dc3545;color:white;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">🔴 Nguy hiểm</span>';
                    $rowStyle = 'background: #fff5f5;';
                } elseif ($sobuoi >= 3) {
                    $badge = '<span style="background:#fd7e14;color:white;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">🟠 Nghiêm trọng</span>';
                    $rowStyle = 'background: #fff8f0;';
                } else {
                    $badge = '<span style="background:#ffc107;color:#333;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">🟡 Cảnh báo</span>';
                    $rowStyle = 'background: #fffdf0;';
                }
            ?>
            <tr style="<?php echo $rowStyle; ?>">
                <td><?php echo $i + 1; ?></td>
                <td><strong><?php echo htmlspecialchars($w['firstName'] . ' ' . $w['lastName']); ?></strong></td>
                <td><?php echo htmlspecialchars($w['admissionNumber']); ?></td>
                <td style="font-weight:bold; font-size:18px; color:<?php echo $sobuoi >= 5 ? '#dc3545' : ($sobuoi >= 4 ? '#fd7e14' : '#ffc107'); ?>">
                    <?php echo $sobuoi; ?> buổi
                </td>
                <td><?php echo $badge; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p style="color:#6c757d; font-size:13px; margin-top:10px;">
        Tổng: <strong><?php echo count($warnings); ?></strong> sinh viên cần chú ý
    </p>
</div>
<?php else: ?>
<div class="table-container" style="margin-top:20px; text-align:center; color:#28a745;">
    <i class="fas fa-check-circle" style="font-size:30px; margin-bottom:10px;"></i>
    <p style="font-size:16px; font-weight:600;">✅ Tất cả sinh viên đều chuyên cần tốt!</p>
</div>
<?php endif; ?>
                    </div>
                    
                </div>
            </div>
            
            <?php include('Includes/footer.php'); ?>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
                // Modal HTML injection
                (function(){
                        var modal = `
                        <div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="attendanceModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="attendanceModalLabel">Danh sách học sinh</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="attendanceListContainer">Đang tải...</div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                        document.body.insertAdjacentHTML('beforeend', modal);
                })();

                function fetchAttendanceList(classId, classArmId, status, date) {
                        date = date || '<?php echo date('Y-m-d'); ?>';
                        var url = 'get_attendance_list.php?classId='+encodeURIComponent(classId)+'&classArmId='+encodeURIComponent(classArmId)+'&status='+encodeURIComponent(status)+'&date='+encodeURIComponent(date);
                        document.getElementById('attendanceListContainer').innerHTML = 'Đang tải...';
                        fetch(url).then(function(r){return r.json();}).then(function(data){
                                if (data.error) {
                                        document.getElementById('attendanceListContainer').innerHTML = '<div class="text-danger">'+data.error+'</div>';
                                        return;
                                }
                                var rows = data.data;
                                if (!rows || rows.length === 0) {
                                        document.getElementById('attendanceListContainer').innerHTML = '<div class="p-3">Không có học sinh.</div>';
                                        return;
                                }
                                var html = '<table class="table table-striped"><thead><tr><th>STT</th><th>Họ và tên</th><th>MSSV</th><th>Trạng thái</th></tr></thead><tbody>';
                                rows.forEach(function(r,i){
                                        html += '<tr><td>'+(i+1)+'</td><td>'+ (r.firstName+' '+(r.lastName||'')) +'</td><td>'+ (r.admissionNumber||'') +'</td><td>'+ (r.status||'') +'</td></tr>';
                                });
                                html += '</tbody></table>';
                                document.getElementById('attendanceListContainer').innerHTML = html;
                        }).catch(function(err){
                                document.getElementById('attendanceListContainer').innerHTML = '<div class="text-danger">Lỗi: '+err.message+'</div>';
                        });
                }

                // attach click handlers
                document.addEventListener('click', function(e){
                        var el = e.target.closest('.stat-card');
                        if (!el) return;
                        var action = el.getAttribute('data-action');
                        var classId = el.getAttribute('data-classid') || '<?php echo intval($classId); ?>';
                        var classArmId = el.getAttribute('data-classarmid') || '<?php echo intval($classArmId); ?>';
                        if (action === 'breakdown') {
                            var statuses = ['present','late','absent','excused'];
                            document.getElementById('attendanceListContainer').innerHTML = 'Đang tải...';
                            Promise.all(statuses.map(function(s){
                                return fetch('get_attendance_list.php?classId='+encodeURIComponent(classId)+'&classArmId='+encodeURIComponent(classArmId)+'&status='+s+'&date=<?php echo date('Y-m-d'); ?>').then(function(r){return r.json();});
                            })).then(function(results){
                                var html = '<ul class="nav nav-tabs" role="tablist">';
                                statuses.forEach(function(s,i){
                                    var count = (results[i] && results[i].data) ? results[i].data.length : 0;
                                    var active = i===0 ? 'active' : '';
                                    var label = s==='present'? 'Có Mặt' : (s==='late'?'Đi Muộn':(s==='absent'?'Vắng':'Có Phép'));
                                    html += '<li class="nav-item"><a class="nav-link '+active+'" id="tab-'+s+'" data-toggle="tab" href="#tabpane-'+s+'" role="tab">'+label+' ('+count+')</a></li>';
                                });
                                html += '</ul><div class="tab-content" style="margin-top:15px;">';
                                statuses.forEach(function(s,i){
                                    var activePane = i===0 ? 'show active' : '';
                                    html += '<div class="tab-pane '+activePane+'" id="tabpane-'+s+'" role="tabpanel">';
                                    var rows = (results[i] && results[i].data) ? results[i].data : [];
                                    if (rows.length === 0) {
                                        html += '<div class="p-3">Không có học sinh.</div>';
                                    } else {
                                        html += '<table class="table table-sm"><thead><tr><th>STT</th><th>Họ và tên</th><th>MSSV</th></tr></thead><tbody>';
                                        rows.forEach(function(r,idx){ html += '<tr><td>'+(idx+1)+'</td><td>'+(r.firstName+' '+(r.lastName||''))+'</td><td>'+(r.admissionNumber||'')+'</td></tr>'; });
                                        html += '</tbody></table>';
                                    }
                                    html += '</div>';
                                });
                                html += '</div>';
                                document.getElementById('attendanceListContainer').innerHTML = html;
                                $('#attendanceModal').modal('show');
                            }).catch(function(err){
                                document.getElementById('attendanceListContainer').innerHTML = '<div class="text-danger">Lỗi: '+err.message+'</div>';
                                $('#attendanceModal').modal('show');
                            });
                            return;
                        }
                        var status = el.getAttribute('data-status');
                        if (!status) return;
                        fetchAttendanceList(classId, classArmId, status);
                        $('#attendanceModal').modal('show');
                });
        // Biểu đồ điểm danh hôm nay
        const ctx1 = document.getElementById('todayChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Có Mặt', 'Đi Muộn', 'Vắng Mặt', 'Có Phép'],
                datasets: [{
                    data: [<?php echo $stats['present']; ?>, <?php echo $stats['late']; ?>, <?php echo $stats['absent']; ?>, <?php echo $stats['excused']; ?>],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                    borderColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    title: {
                        display: true,
                        text: 'Thống Kê Điểm Danh Hôm Nay'
                    }
                }
            }
        });
        
        // Biểu đồ 7 ngày
        const ctx2 = document.getElementById('weekChart').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: [<?php 
                    foreach ($dailyAttendance as $day) {
                        echo "'" . date('d/m', strtotime($day['dateTimeTaken'])) . "',";
                    }
                ?>],
                datasets: [{
                    label: 'Học sinh có mặt',
                    data: [<?php 
                        foreach ($dailyAttendance as $day) {
                            echo $day['attended'] . ",";
                        }
                    ?>],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#28a745'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true },
                    title: {
                        display: true,
                        text: 'Xu Hướng Điểm Danh 7 Ngày'
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script> 
</body>
</html>
