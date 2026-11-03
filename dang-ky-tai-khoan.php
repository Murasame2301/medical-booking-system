<?php
session_start();
require_once 'mysqlConnect.php';

if(!empty($_SESSION['is_logged_in'])){
    header('Location: xem-lich-kham.php');
    exit;
}

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $phonenumber = trim($_POST['sdt'] ?? ' =');
    $rawPassword = trim($_POST['mat_khau'] ?? '');
    $rawRepassword = trim($_POST['nhap_lai_mat_khau' ?? '']);

    if($phonenumber === '' && $rawPassword === '' && $rawRepassword === ''){
        $message = 'Vui lòng nhập đầy đủ thông tin.';
    }
    elseif ($rawPassword !== $rawRepassword){
        $message = 'Mật khẩu không khớp. Vui lòng kiểm tra lại.';
    } else{
        //Mã hóa mật khẩu sau khi đã khớp
        $password = password_hash($rawPassword, PASSWORD_DEFAULT);
        $sql = "INSERT INTO NGUOI_DUNG(mavaitro, sdt, matkhau) VALUES(1, ?, ?)";

        $stmt = $conn->prepare($sql);
        if($stmt){
            $stmt->bind_param("ss", $phonenumber, $password);
            if($stmt->execute()){
                $stmt->close();
                // Chuyển sang phần tạo hồ sơ -- chưa điền
                exit;
            } else{
                if($stmt->errno ==  1062){
                    $message = "<p class='message message-error'>Số điện thoại này đã tồn tại trong hệ thống!</p>";
                } else{
                    $message ="<p class='message message-error'>Lỗi: " . $stmt->error . "</p>";
                }
            }
            $stmt->close();
        } else{
            $message = "<p class='message message-error'>Lỗi chuẩn bị truy vấn: " . htmlspecialchars($conn->error) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký thành viên</title>
</head>
<body>
<!-- <?php include 'menu.php';?> -->
</body>
</html>