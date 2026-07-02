<?php
session_start();
include('../Includes/dbcon.php');
include('../Includes/session.php');

// Tổng số học sinh
$totalStudents = $conn->query("SELECT COUNT(*) as total FROM tblstudents")->fetch_assoc()['total'];

// Tổng số lớp
$totalClasses = $conn->query("SELECT COUNT(*) as total FROM tblclass")->fetch_assoc()['total'];

// Tổng số chi nhánh lớp
$totalClassArms = $conn->query("SELECT COUNT(*) as total FROM tblclassarms")->fetch_assoc()['total'];

// Hôm nay
$today = date('Y-m-d');

// Điểm danh hôm nay toàn trường
$attendanceToday = $conn->query("SELECT status, COUNT(*) as count FROM tblattendance WHERE dateTimeTaken = '$today' GROUP BY status")->fetch_all(MYSQLI_ASSOC);

$statsToday = [
    'present' => 0,
    'late' => 0,
    'absent' => 0,
    'excused' => 0
];

foreach ($attendanceToday as $record) {
    $statsToday[$record['status']] = $record['count'];
}

$totalAttendedToday = $statsToday['present'] + $statsToday['late'];
$totalRecordsToday = array_sum($statsToday);

// Thống kê toàn bộ
$totalAttendance = $conn->query("SELECT COUNT(*) as total FROM tblattendance")->fetch_assoc()['total'];

// Thống kê 7 ngày
$sevenDaysAgo = date('Y-m-d', strtotime('-6 days'));
$dailyStats = $conn->query("
    SELECT dateTimeTaken,
           SUM(IF(status IN ('present', 'late'), 1, 0)) as attended,
           COUNT(*) as total
    FROM tblattendance
    WHERE dateTimeTaken >= '$sevenDaysAgo'
    GROUP BY dateTimeTaken
    ORDER BY dateTimeTaken ASC
")->fetch_all(MYSQLI_ASSOC);

// Tổng số giáo viên được gán lớp
$totalTeachers = $conn->query("SELECT COUNT(DISTINCT teacherId) as total FROM tblclassassignment")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Dashboard Phân Tích - Admin</title>
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
        
        .progress-bar {
            height: 25px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
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
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
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
                        <h1 class="page-title">📊 Dashboard Phân Tích Toàn Trường</h1>
                        <p class="page-subtitle">
                            Cập nhật: <?php echo date('d/m/Y H:i'); ?>
                        </p>
                    </div>
                    
                    <!-- Thống kê tổng quát -->
                    <h3 style="margin-top: 30px; margin-bottom: 15px;">📈 Thống Kê Tổng Quát</h3>
                    <div class="stat-row">
                        <div class="stat-card" data-status="present" data-classid="0" data-classarmid="0">
                            <div class="stat-card-icon" style="color: #007bff;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-card-header">Tổng Số Học Sinh</div>
                            <div class="stat-card-value"><?php echo $totalStudents; ?></div>
                        </div>
                        
                        <div class="stat-card" data-status="late" data-classid="0" data-classarmid="0">
                            <div class="stat-card-icon" style="color: #6f42c1;">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stat-card-header">Tổng Số Lớp</div>
                            <div class="stat-card-value"><?php echo $totalClasses; ?></div>
                        </div>
                        
                        <div class="stat-card" data-status="absent" data-classid="0" data-classarmid="0">
                            <div class="stat-card-icon" style="color: #e83e8c;">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="stat-card-header">Chi Nhánh Lớp</div>
                            <div class="stat-card-value"><?php echo $totalClassArms; ?></div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-card-icon" style="color: #fd7e14;">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="stat-card-header">Giáo Viên</div>
                            <div class="stat-card-value"><?php echo $totalTeachers; ?></div>
                        </div>
                    </div>
                    
                    <!-- Thống kê hôm nay -->
                    <h3 style="margin-top: 30px; margin-bottom: 15px;">📅 Thống Kê Hôm Nay (<?php echo date('d/m/Y'); ?>)</h3>
                    <div class="stat-row">
                        <div class="stat-card" data-action="breakdown" data-classid="0" data-classarmid="0">
                            <div class="stat-card-icon stat-present">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-card-header">Có Mặt</div>
                            <div class="stat-card-value"><?php echo $statsToday['present']; ?></div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-card-icon stat-late">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-card-header">Đi Muộn</div>
                            <div class="stat-card-value"><?php echo $statsToday['late']; ?></div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-card-icon stat-absent">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-card-header">Vắng Mặt</div>
                            <div class="stat-card-value"><?php echo $statsToday['absent']; ?></div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-card-icon stat-excused">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="stat-card-header">Có Phép</div>
                            <div class="stat-card-value"><?php echo $statsToday['excused']; ?></div>
                        </div>
                    </div>
                    
                    <!-- Tỷ lệ điểm danh hôm nay -->
                    <div class="stat-row">
                        <div class="stat-card">
                            <div class="stat-card-icon" style="color: #28a745;">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-card-header">Tỷ Lệ Điểm Danh</div>
                            <div class="stat-card-value"><?php echo $totalRecordsToday > 0 ? round(($totalAttendedToday / $totalRecordsToday) * 100, 2) : 0; ?>%</div>
                            <p style="color: #6c757d; margin: 10px 0 0 0; font-size: 14px;">
                                <?php echo $totalAttendedToday; ?>/<?php echo $totalRecordsToday; ?> học sinh
                            </p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-card-icon" style="color: #dc3545;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="stat-card-header">Tổng Bản Ghi</div>
                            <div class="stat-card-value"><?php echo $totalAttendance; ?></div>
                        </div>
                    </div>
                    
                    <!-- Biểu đồ hôm nay -->
                    <div class="chart-container">
                        <canvas id="todayChart"></canvas>
                    </div>
                    
                    <!-- Biểu đồ 7 ngày -->
                    <div class="chart-container">
                        <canvas id="weekChart"></canvas>
                    </div>
                    
                </div>
            </div>
            
            <?php include('Includes/footer.php'); ?>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
                // Admin modal for attendance lists
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

                function fetchAttendanceListAdmin(classId, classArmId, status, date) {
                        date = date || '<?php echo date('Y-m-d'); ?>';
                        var url = '../ClassTeacher/get_attendance_list.php?classId='+encodeURIComponent(classId)+'&classArmId='+encodeURIComponent(classArmId)+'&status='+encodeURIComponent(status)+'&date='+encodeURIComponent(date);
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

                document.addEventListener('click', function(e){
                        var el = e.target.closest('.stat-card');
                        if (!el) return;
                        var action = el.getAttribute('data-action');
                        var classId = el.getAttribute('data-classid') || 0;
                        var classArmId = el.getAttribute('data-classarmid') || 0;
                        if (action === 'breakdown') {
                            var statuses = ['present','late','absent','excused'];
                            document.getElementById('attendanceListContainer').innerHTML = 'Đang tải...';
                            Promise.all(statuses.map(function(s){
                                return fetch('../ClassTeacher/get_attendance_list.php?classId='+encodeURIComponent(classId)+'&classArmId='+encodeURIComponent(classArmId)+'&status='+s+'&date=<?php echo date('Y-m-d'); ?>').then(function(r){return r.json();});
                            })).then(function(results){
                                var html = '<ul class="nav nav-tabs" role="tablist">';
                                statuses.forEach(function(s,i){
                                    var count = (results[i] && results[i].data) ? results[i].data.length : 0;
                                    var active = i===0 ? 'active' : '';
                                    var label = s==='present'? 'Có Mặt' : (s==='late'?'Đi Muộn':(s==='absent'?'Vắng':'Có Phép'));
                                    html += '<li class="nav-item'><a class="nav-link '+active+'" id="tab-'+s+'" data-toggle="tab" href="#tabpane-'+s+'" role="tab">'+label+' ('+count+')</a></li>';
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
                        fetchAttendanceListAdmin(classId, classArmId, status);
                        $('#attendanceModal').modal('show');
                });
        // Biểu đồ hôm nay
        const ctx1 = document.getElementById('todayChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Có Mặt', 'Đi Muộn', 'Vắng Mặt', 'Có Phép'],
                datasets: [{
                    data: [<?php echo $statsToday['present']; ?>, <?php echo $statsToday['late']; ?>, <?php echo $statsToday['absent']; ?>, <?php echo $statsToday['excused']; ?>],
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
                        text: 'Thống Kê Điểm Danh Hôm Nay - Toàn Trường'
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
                    foreach ($dailyStats as $day) {
                        echo "'" . date('d/m', strtotime($day['dateTimeTaken'])) . "',";
                    }
                ?>],
                datasets: [{
                    label: 'Học sinh có mặt',
                    data: [<?php 
                        foreach ($dailyStats as $day) {
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
                        text: 'Xu Hướng Điểm Danh 7 Ngày - Toàn Trường'
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
