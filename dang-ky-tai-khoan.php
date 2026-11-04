<?php
session_start();
require_once __DIR__ . '/include/mysqlConnect.php';

if(!empty($_SESSION['is_logged_in'])){
    header('Location: xem-lich-kham.php');
    exit;
}

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $fullname = trim($_POST['hoten'] ?? '');
    $phonenumber = trim($_POST['sdt'] ?? '');
    $rawPassword = $_POST['mat_khau'] ?? '';
    $rawRepassword = $_POST['nhap_lai_mat_khau'] ?? '';

    if($phonenumber === '' || $rawPassword === '' || $rawRepassword === ''){
        $message = 'Vui lòng nhập đầy đủ thông tin.';
    }
    elseif ($rawPassword !== $rawRepassword){
        $message = 'Mật khẩu không khớp. Vui lòng kiểm tra lại.';
    } else{
        //Mã hóa mật khẩu sau khi đã khớp
        $password = password_hash($rawPassword, PASSWORD_DEFAULT);
        $sql = "INSERT INTO NGUOI_DUNG(mavaitro, hoten, sdt, matkhau) VALUES(1, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if($stmt){
            $stmt->bind_param("sss", $fullname, $phonenumber, $password);
            if($stmt->execute()){
                $manguoidung = $conn->insert_id;

                $_SESSION['manguoidung'] = $manguoidung;
                
                $stmt->close();
                header('Location: tao-ho-so.php');
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
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký thành viên</title>
</head>
<body>
    <!-- Menu sẽ được thêm sau. -->

    <main>
        <h1>Đăng ký thành viên</h1>
        <p>Vui lòng điền thông tin bên dưới để tạo tài khoản.</p>

        <?php if ($message !== ''): ?>
            <div role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div>
                <label for="hoten">Họ và tên</label>
                <input
                    type="text"
                    id="hoten"
                    name="hoten"
                    value="<?php echo htmlspecialchars($_POST['hoten'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    autocomplete="name"
                    required
                >
            </div>

            <div>
                <label for="sdt">Số điện thoại</label>
                <input
                    type="tel"
                    id="sdt"
                    name="sdt"
                    value="<?php echo htmlspecialchars($_POST['sdt'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    autocomplete="tel"
                    required
                >
            </div>

            <div>
                <label for="mat_khau">Mật khẩu</label>
                <input
                    type="password"
                    id="mat_khau"
                    name="mat_khau"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div>
                <label for="nhap_lai_mat_khau">Nhập lại mật khẩu</label>
                <input
                    type="password"
                    id="nhap_lai_mat_khau"
                    name="nhap_lai_mat_khau"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button type="submit">Đăng ký</button>
        </form>

        <p>Đã có tài khoản? <a href="dang-nhap.php">Đăng nhập</a></p>
    </main>
</body>
</html>