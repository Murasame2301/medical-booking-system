<?php
session_start();
require_once __DIR__ . '/include/mysqlConnect.php';

if(!empty($_SESSION['is_logged_in'])){
    header('Location: xem-lich-kham.php');
    exit;
}

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $phonenumber = trim($_POST['sdt'] ?? '');
    $rawPassword = $_POST['mat_khau'] ?? '';

    if($phonenumber === '' || $rawPassword === ''){
        $message = 'Vui lòng nhập đầy đủ thông tin.';
    } else{
        //Kiểm tra thông tin người dùng đăng nhập có hợp lệ không
        $sql = "SELECT manguoidung, sdt, matkhau FROM NGUOI_DUNG WHERE sdt = ?";

        $stmt = $conn->prepare($sql);
        if($stmt){
            $stmt->bind_param('s', $phonenumber);
            if($stmt->execute()){
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if($user && password_verify($rawPassword, $user['matkhau'])){
                    //Đăng nhập thành công
                    session_regenerate_id(true);
                    $_SESSION['manguoidung'] = $user['manguoidung'];
                    $_SESSION['is_logged_in'] = true;
                    
                    $stmt->close();
                    header('Location: xem-lich-kham.php');
                    exit;
                } else{
                    $message = 'Số điện thoại hoặc mật khẩu không đúng.';
                }
            } else {
                $message = 'Không thể kiểm tra thông tin đăng nhập. Vui lòng thử lại.';
            }
            $stmt->close();
        } else{
            $message = 'Không thể chuẩn bị truy vấn. Vui lòng thử lại.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
</head>
<body>
    <!-- Menu sẽ được thêm sau. -->

    <main>
        <h1>Đăng nhập</h1>

        <?php if ($message !== ''): ?>
            <div role="alert">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
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
                    autocomplete="current-password"
                    required
                >
            </div>
            <button type="submit">Đăng nhập</button>
        </form>
    </main>
</body>
</html>