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
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="assets/js/auth.js" defer></script>
</head>
<body>
    <?php require __DIR__ . '/include/auth-header.php'; ?>

    <main class="auth-layout">
        <?php require __DIR__ . '/include/auth-story.php'; ?>
        <section class="auth-panel" aria-labelledby="auth-title">
        <div class="auth-content">
        <h1 id="auth-title">Chào mừng bạn trở lại</h1>
        <p class="form-intro">Đăng nhập để đặt lịch và theo dõi lịch khám.</p>

        <?php if ($message !== ''): ?>
            <div class="form-alert" role="alert">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="field">
                <label for="sdt">Số điện thoại</label>
                <input
                    type="tel"
                    id="sdt"
                    name="sdt"
                    value="<?php echo htmlspecialchars($_POST['sdt'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    autocomplete="tel"
                    placeholder="Nhập số điện thoại của bạn"
                    required
                >
            </div>

            <div class="field">
                <label for="mat_khau">Mật khẩu</label>
                <div class="password-control">
                <input
                    type="password"
                    id="mat_khau"
                    name="mat_khau"
                    autocomplete="current-password"
                    placeholder="Nhập mật khẩu"
                    required
                >
                <?php $passwordFieldId = 'mat_khau'; require __DIR__ . '/include/auth-password-toggle.php'; ?>
                </div>
            </div>
            <button class="primary-button" type="submit">Đăng nhập</button>
        </form>
        <p class="auth-switch">Chưa có tài khoản? <a href="dang-ky-tai-khoan.php">Đăng ký ngay</a></p>
        </div>
        <p class="auth-footer">Cổng đặt lịch khám bệnh trực tuyến</p>
        </section>
    </main>
</body>
</html>
