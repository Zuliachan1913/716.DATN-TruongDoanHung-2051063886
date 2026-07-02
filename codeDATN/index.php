<?php 
include 'Includes/dbcon.php';
session_start();
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="img/logo/TL.png" rel="icon">
    <title>Học viện Thủy Lợi</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    min-height:100vh;

    background:
    linear-gradient(
        135deg,
        #eef5ff,
        #dbeafe,
        #f8fbff
    );

    display:flex;
    justify-content:center;
    align-items:center;

    overflow:hidden;
    position:relative;
}

/* hiệu ứng nền sáng */

body::before{
    content:'';
    position:absolute;
    width:500px;
    height:500px;
    background:#93c5fd;
    border-radius:50%;
    filter:blur(140px);
    top:-150px;
    left:-150px;
    opacity:0.4;
}

body::after{
    content:'';
    position:absolute;
    width:450px;
    height:450px;
    background:#bfdbfe;
    border-radius:50%;
    filter:blur(140px);
    bottom:-150px;
    right:-150px;
    opacity:0.5;
}

.login-container{
    width:100%;
    max-width:470px;
    padding:20px;
    position:relative;
    z-index:2;
}

.login-card{

    background:rgba(255,255,255,0.75);

    backdrop-filter:blur(18px);

    border:1px solid rgba(255,255,255,0.6);

    border-radius:30px;

    padding:45px 38px;

    box-shadow:
    0 10px 35px rgba(0,0,0,0.12);

    animation:fadeIn 0.8s ease;
}

@keyframes fadeIn{

    from{
        opacity:0;
        transform:translateY(25px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

.logo{
    width:100px;
    height:100px;
    object-fit:contain;
    margin-bottom:18px;
}

.title{
    color:#1e3a8a;
    font-size:40px;
    font-weight:700;
    margin-bottom:10px;
    text-align:center;
}

.subtitle{
    text-align:center;
    color:#475569;
    margin-bottom:32px;
    font-size:16px;
}

.form-control{

    height:58px;

    border:none;

    border-radius:16px;

    background:#f8fbff;

    padding-left:18px;

    margin-bottom:20px;

    font-size:16px;

    transition:0.3s;

    box-shadow:
    inset 0 2px 5px rgba(0,0,0,0.04);
}

.form-control:focus{

    background:white;

    box-shadow:
    0 0 0 4px rgba(59,130,246,0.2);

    transform:translateY(-2px);
}

.btn-login{

    width:100%;

    height:58px;

    border:none;

    border-radius:16px;

    background:
    linear-gradient(
        135deg,
        #60a5fa,
        #2563eb
    );

    color:white;

    font-size:20px;

    font-weight:600;

    transition:0.35s;

    letter-spacing:0.5px;
}

.btn-login:hover{

    transform:translateY(-3px);

    box-shadow:
    0 10px 20px rgba(37,99,235,0.25);
}

.footer-text{
    text-align:center;
    margin-top:28px;
    color:#64748b;
    font-size:14px;
}

.alert{
    margin-top:18px;
    border-radius:14px;
}

.text-center{
    text-align:center;
}

</style>


</head>

<body>

<div class="login-container">

    <div class="login-card">

        <div class="text-center">

    <img src="img/logo/TL.png" class="logo">

    <div class="title" style="font-size:32px;">
    Học viện Thủy Lợi
</div>

    <div class="subtitle">
        Hệ thống điểm danh sinh viên bằng QR Code
    </div>

</div>

        <form method="POST" action="">

            <select required name="userType" class="form-control">
                <option value="">-- Chọn phân quyền --</option>
                <option value="Administrator">Admin</option>
                <option value="ClassTeacher">Giáo viên</option>
            </select>

            <input type="text"
                   class="form-control"
                   required
                   name="username"
                   placeholder="Nhập email">

            <input type="password"
                   name="password"
                   required
                   class="form-control"
                   placeholder="Nhập mật khẩu">

            <button type="submit"
        name="login"
        class="btn-login">

            <i class="fas fa-sign-in-alt"></i>
    Đăng nhập

</button>

        

        </form>

        <?php

        if(isset($_POST['login'])){

            $userType = $_POST['userType'];
            $username = $_POST['username'];
            $password = md5($_POST['password']);

            if($userType == "Administrator"){

                $query = "SELECT * FROM tbladmin
                          WHERE emailAddress='$username'
                          AND password='$password'";

                $rs = $conn->query($query);
                $num = $rs->num_rows;
                $rows = $rs->fetch_assoc();

                if($num > 0){

                    $_SESSION['userId'] = $rows['Id'];
                    $_SESSION['firstName'] = $rows['firstName'];
                    $_SESSION['lastName'] = $rows['lastName'];
                    $_SESSION['emailAddress'] = $rows['emailAddress'];

                    echo "<script>
                    window.location='Admin/index.php'
                    </script>";

                }else{

                    echo "<div class='alert alert-danger'>
                    Sai tài khoản hoặc mật khẩu!
                    </div>";

                }

            }

            else if($userType == "ClassTeacher"){

                $query = "SELECT * FROM tblclassteacher
                          WHERE emailAddress='$username'
                          AND password='$password'";

                $rs = $conn->query($query);
                $num = $rs->num_rows;
                $rows = $rs->fetch_assoc();

                if($num > 0){

                    $_SESSION['userId'] = $rows['Id'];
                    $_SESSION['firstName'] = $rows['firstName'];
                    $_SESSION['lastName'] = $rows['lastName'];
                    $_SESSION['emailAddress'] = $rows['emailAddress'];
                    $_SESSION['classId'] = $rows['classId'];
                    $_SESSION['classArmId'] = $rows['classArmId'];

                    echo "<script>
                    window.location='ClassTeacher/index.php'
                    </script>";

                }else{

                    echo "<div class='alert alert-danger'>
                    Sai tài khoản hoặc mật khẩu!
                    </div>";

                }

            }else{

                echo "<div class='alert alert-danger'>
                Vui lòng chọn phân quyền!
                </div>";

            }

        }

        ?>

        <div class="footer-text">
            © 2026 Học viện Thủy Lợi
        </div>

    </div>

</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>