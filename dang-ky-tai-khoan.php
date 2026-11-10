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
                    $message = 'Số điện thoại này đã có tài khoản. Vui lòng đăng nhập hoặc sử dụng số khác.';
                } else{
                    $message = 'Không thể tạo tài khoản. Vui lòng thử lại.';
                }
            }
            $stmt->close();
        } else{
            $message = 'Không thể tạo tài khoản. Vui lòng thử lại.';
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
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="assets/js/auth.js" defer></script>
</head>
<body class="register-page">
    <?php require __DIR__ . '/include/auth-header.php'; ?>

    <main class="auth-layout">
        <?php require __DIR__ . '/include/auth-story.php'; ?>
        <section class="auth-panel" aria-labelledby="auth-title">
        <div class="auth-content">
        <h1 id="auth-title">Tạo tài khoản của bạn</h1>
        <p class="form-intro">Để việc đặt lịch khám thuận tiện hơn.</p>

        <?php if ($message !== ''): ?>
            <div class="form-alert" role="alert">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="field">
                <label for="hoten">Họ và tên</label>
                <input
                    type="text"
                    id="hoten"
                    name="hoten"
                    value="<?php echo htmlspecialchars($_POST['hoten'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    autocomplete="name"
                    placeholder="Nhập họ và tên của bạn"
                    required
                >
            </div>

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
                    autocomplete="new-password"
                    placeholder="Tạo mật khẩu"
                    required
                >
                <?php $passwordFieldId = 'mat_khau'; require __DIR__ . '/include/auth-password-toggle.php'; ?>
                </div>
            </div>

            <div class="field">
                <label for="nhap_lai_mat_khau">Nhập lại mật khẩu</label>
                <div class="password-control">
                <input
                    type="password"
                    id="nhap_lai_mat_khau"
                    name="nhap_lai_mat_khau"
                    autocomplete="new-password"
                    placeholder="Nhập lại mật khẩu vừa tạo"
                    required
                >
                <?php $passwordFieldId = 'nhap_lai_mat_khau'; require __DIR__ . '/include/auth-password-toggle.php'; ?>
                </div>
            </div>

            <button class="primary-button" type="submit">Tạo tài khoản</button>
            <p class="form-note">Sau khi tạo tài khoản, bạn sẽ được chuyển đến bước tạo hồ sơ khám bệnh.</p>
        </form>

        <p class="auth-switch">Đã có tài khoản? <a href="dang-nhap.php">Đăng nhập</a></p>
        </div>
        <p class="auth-footer">Cổng đặt lịch khám bệnh trực tuyến</p>
        </section>
    </main>
</body>
</html>
