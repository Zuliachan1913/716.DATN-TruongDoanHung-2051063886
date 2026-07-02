<?php

include '../Includes/dbcon.php';

$message = "";

if(isset($_GET['token'])){

    $token = $_GET['token'];

    $query = mysqli_query($conn,
    "SELECT * FROM qr_tokens WHERE token='$token'");

    $row = mysqli_fetch_array($query);

    if($row){
        // check created_at exists and enforce 1 hour expiry
        $created = isset($row['created_at']) ? $row['created_at'] : null;
        if ($created) {
            $created_ts = strtotime($created);
            $now_ts = time();
            $elapsed = $now_ts - $created_ts; // seconds
            if ($elapsed > 3600) {
                die("<html><head><title>QR Hết Hạn</title></head><body style='font-family:Arial;display:flex;justify-content:center;align-items:center;height:100vh;background:#0f172a;color:white'><div style='background:#1e293b;padding:40px;border-radius:20px;text-align:center;width:350px'><h1 style='color:#ef4444'>QR Không Hợp Lệ</h1><p>QR đã hết hạn (hết 1 giờ). Nếu bạn muốn mở lại, tạo QR mới.</p></div></body></html>");
            }
        }

        $classId = $row['classId'];

        if(isset($_POST['submit'])){

            $admissionNo = $_POST['admissionNo'];

            $date = date("Y-m-d");

            // CHECK ĐÃ ĐIỂM DANH CHƯA
            $check = mysqli_query($conn,
            "SELECT * FROM tblattendance
             WHERE admissionNo='$admissionNo'
             AND classId='$classId'
             AND dateTimeTaken='$date'");

            if(mysqli_num_rows($check) > 0){
                $message = "<div class='alert error'>⚠️ Bạn đã điểm danh hôm nay!</div>";
            }else{
                // determine status based on elapsed time since QR creation (if available)
                $status = 'present';
                if (isset($created_ts)) {
                    if ($elapsed <= 600) { // 10 minutes
                        $status = 'present';
                    } elseif ($elapsed <= 3600) { // up to 1 hour
                        $status = 'late';
                    } else {
                        // more than 1 hour - consider expired and do not record
                        $message = "<div class='alert error'>QR đã hết hạn (quá 1 giờ). Không thể điểm danh.</div>";
                        echo $message;
                        exit;
                    }
                }

                // attempt to get classArmId from token row if present
                $classArmId = isset($row['classArmId']) ? $row['classArmId'] : '0';

                mysqli_query($conn,
                "INSERT INTO tblattendance
                (id_admin,admissionNo,classId,classArmId,diligently,status,dateTimeTaken)

                VALUES
                ('0','$admissionNo','$classId','$classArmId','0','$status','$date')");

                // do NOT mark token used so multiple students can scan same QR within expiry

                $message = "<div class='alert success'>✅ Điểm danh thành công! Trạng thái: $status</div>";
            }
        }

    }else{

        die("

        <html>

        <head>

        <title>QR Hết Hạn</title>

        <style>

        body{
            background:#0f172a;
            color:white;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            font-family:Arial;
        }

        .box{
            background:#1e293b;
            padding:40px;
            border-radius:20px;
            text-align:center;
            width:350px;
        }

        h1{
            color:#ef4444;
        }

        </style>

        </head>

        <body>

        <div class='box'>

        <h1>QR Không Hợp Lệ</h1>

        <p>QR đã hết hạn hoặc đã được sử dụng.</p>

        </div>

        </body>

        </html>

        ");
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Điểm Danh QR</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Poppins',sans-serif;
    min-height:100vh;
    background:linear-gradient(135deg,#1e3a8a,#2563eb,#3b82f6);
    display:flex;
    justify-content:center;
    align-items:center;
    overflow:hidden;
}

.bg-circle{
    position:absolute;
    border-radius:50%;
    background:rgba(255,255,255,0.08);
}

.circle1{
    width:300px;
    height:300px;
    top:-100px;
    left:-100px;
}

.circle2{
    width:250px;
    height:250px;
    bottom:-80px;
    right:-80px;
}

.container{
    width:90%;
    max-width:430px;
    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(15px);
    border:1px solid rgba(255,255,255,0.2);
    border-radius:30px;
    padding:40px 35px;
    box-shadow:0 10px 40px rgba(0,0,0,0.25);
    text-align:center;
    animation:fadeIn 0.8s ease;
}

@keyframes fadeIn{
    from{
        transform:translateY(20px);
        opacity:0;
    }

    to{
        transform:translateY(0);
        opacity:1;
    }
}

.logo{
    width:95px;
    height:95px;
    object-fit:cover;
    border-radius:50%;
    border:4px solid rgba(255,255,255,0.3);
    margin-bottom:20px;
    background:white;
    padding:8px;
}

.title{
    font-size:32px;
    font-weight:700;
    color:white;
    margin-bottom:8px;
}

.subtitle{
    color:rgba(255,255,255,0.8);
    margin-bottom:35px;
    font-size:15px;
}

.input-group{
    position:relative;
    margin-bottom:25px;
}

input{
    width:100%;
    padding:18px 20px;
    border:none;
    border-radius:18px;
    background:rgba(255,255,255,0.18);
    color:white;
    font-size:16px;
    outline:none;
    transition:0.3s;
    border:2px solid transparent;
}

input:focus{
    border-color:white;
    background:rgba(255,255,255,0.25);
}

input::placeholder{
    color:rgba(255,255,255,0.7);
}

button{
    width:100%;
    padding:18px;
    border:none;
    border-radius:18px;
    background:white;
    color:#2563eb;
    font-size:18px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:translateY(-2px);
    background:#eff6ff;
}

.alert{
    padding:16px;
    border-radius:16px;
    margin-bottom:25px;
    font-weight:500;
    animation:fadeIn 0.5s ease;
}

.success{
    background:rgba(34,197,94,0.2);
    color:#dcfce7;
    border:1px solid rgba(34,197,94,0.4);
}

.error{
    background:rgba(239,68,68,0.2);
    color:#fee2e2;
    border:1px solid rgba(239,68,68,0.4);
}

.footer{
    margin-top:30px;
    color:rgba(255,255,255,0.7);
    font-size:14px;
}

.scan-icon{
    font-size:50px;
    margin-bottom:18px;
}

</style>

</head>

<body>

<div class="bg-circle circle1"></div>
<div class="bg-circle circle2"></div>

<div class="container">

<img src="../img/logo/TL.png" class="logo">

<div class="scan-icon">
📲
</div>

<div class="title">
QR Attendance
</div>

<div class="subtitle">
Quét mã QR để điểm danh vào lớp học
</div>

<?php echo $message; ?>

<form method="POST">

<div class="input-group">

<input type="text"
name="admissionNo"
placeholder="Nhập mã sinh viên..."
required>

</div>

<button type="submit" name="submit">

Xác Nhận Điểm Danh

</button>

</form>

<div class="footer">

Học viện TL © 2026

</div>

</div>

</body>

</html>

