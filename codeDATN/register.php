<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>

<body>
    <h2>Register</h2>
    <form action="register.php" method="post">
        <label for="firstName">First Name:</label><br>
        <input type="text" id="firstName" name="firstName" required><br><br>
        
        <label for="lastName">Last Name:</label><br>
        <input type="text" id="lastName" name="lastName" required><br><br>
        
        <label for="emailAddress">Email Address:</label><br>
        <input type="email" id="emailAddress" name="emailAddress" required><br><br>
        
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        
        <input type="submit" value="Register" name="register">
    </form>
</body>

</html>
<?php
include 'Includes/dbcon.php'; // Đảm bảo rằng file kết nối cơ sở dữ liệu đã được include vào đúng

if(isset($_POST['register'])) {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $emailAddress = $_POST['emailAddress'];
    $password = $_POST['password'];
    
    // Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu chưa
    $checkQuery = "SELECT * FROM tbladmin WHERE emailAddress = '$emailAddress'";
    $checkResult = $conn->query($checkQuery);
    
    if($checkResult->num_rows > 0) {
        echo "<p>Email đã tồn tại. Vui lòng chọn email khác.</p>";
    } else {
        // Mã hóa mật khẩu trước khi lưu vào cơ sở dữ liệu (ví dụ sử dụng MD5)
        $hashedPassword = md5($password);
        
        // Thêm thông tin người dùng mới vào cơ sở dữ liệu
        $insertQuery = "INSERT INTO tbladmin (firstName, lastName, emailAddress, password) VALUES ('$firstName', '$lastName', '$emailAddress', '$hashedPassword')";
        
        if($conn->query($insertQuery) === TRUE) {
            echo "<p>Đăng ký thành công!</p>";
            // Redirect hoặc thông báo người dùng đã đăng ký thành công
        } else {
            echo "Lỗi: " . $insertQuery . "<br>" . $conn->error;
        }
    }
}

$conn->close();
?>
