<?php

error_reporting(0);

include '../Includes/dbcon.php';

include '../phpqrcode/qrlib.php';

$classId = 4;

// TOKEN RANDOM

$token = md5(uniqid(rand(), true));

// Ensure qr_tokens has a created_at column (best-effort)
@mysqli_query($conn, "ALTER TABLE qr_tokens ADD COLUMN IF NOT EXISTS created_at DATETIME DEFAULT CURRENT_TIMESTAMP");

mysqli_query($conn,

"INSERT INTO qr_tokens (token,classId,created_at)

VALUES ('$token','$classId', NOW())");

// LINK QR

$link = "http://192.168.130.87:8080/ClassTeacher/scanQR.php?token=$token";

// FILE QR

$fileName = "qr_" . time() . ".png";

$filePath = "../temp/" . $fileName;

// TẠO QR

QRcode::png($link, $filePath);

?>

<!DOCTYPE html>
<html>

<head>

<title>Generate QR</title>

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
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(135deg,#1e3a8a,#2563eb,#3b82f6);
    overflow:hidden;
}

.circle{
    position:absolute;
    border-radius:50%;
    background:rgba(255,255,255,0.08);
}

.circle1{
    width:350px;
    height:350px;
    top:-120px;
    left:-120px;
}

.circle2{
    width:250px;
    height:250px;
    bottom:-80px;
    right:-80px;
}

.container{
    width:90%;
    max-width:450px;
    background:rgba(255,255,255,0.12);
    border:1px solid rgba(255,255,255,0.2);
    backdrop-filter:blur(15px);
    border-radius:30px;
    padding:40px;
    text-align:center;
    box-shadow:0 10px 40px rgba(0,0,0,0.25);
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
    width:90px;
    height:90px;
    object-fit:cover;
    border-radius:50%;
    background:white;
    padding:8px;
    margin-bottom:20px;
}

.title{
    color:white;
    font-size:32px;
    font-weight:700;
    margin-bottom:10px;
}

.subtitle{
    color:rgba(255,255,255,0.8);
    margin-bottom:30px;
    font-size:15px;
}

.qr-box{
    background:white;
    padding:20px;
    border-radius:25px;
    display:inline-block;
    margin-bottom:25px;
    box-shadow:0 8px 25px rgba(0,0,0,0.15);
}

.qr-box img{
    width:260px;
    height:260px;
}

.info{
    color:white;
    margin-bottom:25px;
    font-size:14px;
    line-height:1.8;
}

.info span{
    color:#bfdbfe;
}

button{
    width:100%;
    padding:17px;
    border:none;
    border-radius:18px;
    background:white;
    color:#2563eb;
    font-size:17px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:translateY(-2px);
    background:#eff6ff;
}

.footer{
    margin-top:25px;
    color:rgba(255,255,255,0.7);
    font-size:14px;
}

.qr-icon{
    font-size:45px;
    margin-bottom:10px;
}

</style>

</head>

<body>

<div class="circle circle1"></div>
<div class="circle circle2"></div>

<div class="container">

<img src="../img/logo/TL.png" class="logo">

<div class="qr-icon">
📲
</div>

<div class="title">
QR Điểm Danh
</div>

<div class="subtitle">
Quét mã QR để xác nhận tham gia lớp học
</div>

<div class="qr-box">

<img src="http://localhost:8080/temp/<?php echo $fileName; ?>">

</div>

<div class="info">

<div>
Token:
<span>

<?php echo substr($token,0,15); ?>...

</span>
</div>

<div>
Thời gian tạo:
<span>

<?php echo date("H:i:s d-m-Y"); ?>

</span>
</div>

</div>

<a href="generateQR.php">

<button>

🔄 Tạo QR Mới

</button>

</a>

<div class="footer">

Học viện TL © 2026

</div>

</div>

</body>

</html>