<?php
session_start();
require_once __DIR__ . "/include/mysqlConnect.php";

$manguoidung = $_SESSION['manguoidung'] ?? null;

if(empty($manguoidung)){
    header('Location: dang-nhap.php');
    exit;
}

$message = "";
$userFound = false;
$hoten = "";
$ngaysinh = "";
$gioitinh = "";
$email = "";
$diachi = "";
$bhyt = "";
$mahoso = null;

//Lấy thông tin tài khoản và hồ sơ hiện tại của bệnh nhân
$sql = "SELECT nd.hoten, nd.ngaysinh, nd.gioitinh, nd.email, nd.diachi,
               hs.mahoso, hs.sobhyt
        FROM NGUOI_DUNG nd
        LEFT JOIN HO_SO_BENH hs ON hs.manguoidung = nd.manguoidung
        WHERE nd.manguoidung = ? AND nd.mavaitro = 1
        ORDER BY hs.mahoso DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
if($stmt){
    $stmt->bind_param('i', $manguoidung);
    if($stmt->execute()){
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if($user){
            $userFound = true;
            $hoten = $user['hoten'] ?? '';
            $ngaysinh = $user['ngaysinh'] ?? '';
            $gioitinh = $user['gioitinh'] ?? '';
            $email = $user['email'] ?? '';
            $diachi = $user['diachi'] ?? '';
            $mahoso = $user['mahoso'] ?? null;
            $bhyt = $user['sobhyt'] ?? '';
        } else{
            $message = 'Không tìm thấy thông tin bệnh nhân. Vui lòng đăng nhập lại.';
        }
    } else{
        $message = 'Không thể tải thông tin hồ sơ. Vui lòng thử lại.';
    }
    $stmt->close();
} else{
    $message = 'Không thể chuẩn bị dữ liệu hồ sơ. Vui lòng thử lại.';
}

if($_SERVER["REQUEST_METHOD"] == "POST" && $userFound){
    $ngaysinh = trim($_POST['ngaysinh'] ?? '');
    $gioitinh = trim($_POST['gioitinh'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $diachi = trim($_POST['diachi'] ?? '');
    $bhyt = trim($_POST['bhyt'] ?? '');

    $date = DateTime::createFromFormat('!Y-m-d', $ngaysinh);
    $isValidDate = $date && $date->format('Y-m-d') === $ngaysinh && $ngaysinh <= date('Y-m-d');

    if($ngaysinh === '' || $gioitinh === '' || $diachi === '' || $bhyt === ''){
        $message = 'Vui lòng bổ sung những thông tin bắt buộc.';
    }
    elseif(!$isValidDate){
        $message = 'Ngày sinh không hợp lệ.';
    }
    elseif($gioitinh !== 'nam' && $gioitinh !== 'nu'){
        $message = 'Vui lòng chọn giới tính hợp lệ.';
    }
    elseif($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = 'Địa chỉ email không hợp lệ.';
    }
    elseif(strlen($email) > 100){
        $message = 'Email không được dài quá 100 ký tự.';
    }
    elseif(strlen($diachi) > 255){
        $message = 'Địa chỉ không được dài quá 255 ký tự.';
    }
    elseif(strlen($bhyt) > 20){
        $message = 'Số BHYT không được dài quá 20 ký tự.';
    }
    else{
        $conn->begin_transaction();
        $saveError = '';
        $errorCode = 0;

        //Cập nhật thông tin cá nhân trong tài khoản
        $updateUserSql = "UPDATE NGUOI_DUNG
                          SET ngaysinh = ?, gioitinh = ?, email = NULLIF(?, ''), diachi = ?
                          WHERE manguoidung = ? AND mavaitro = 1";
        $updateUserStmt = $conn->prepare($updateUserSql);
        if($updateUserStmt){
            $updateUserStmt->bind_param('ssssi', $ngaysinh, $gioitinh, $email, $diachi, $manguoidung);
            if(!$updateUserStmt->execute()){
                $saveError = 'Không thể cập nhật thông tin cá nhân.';
                $errorCode = $updateUserStmt->errno;
            }
            $updateUserStmt->close();
        } else{
            $saveError = 'Không thể chuẩn bị cập nhật thông tin cá nhân.';
        }

        //Tạo dữ liệu bệnh nhân nếu tài khoản chưa có
        if($saveError === ''){
            $patientSql = "INSERT INTO BENH_NHAN(manguoidung)
                           VALUES(?)
                           ON DUPLICATE KEY UPDATE manguoidung = manguoidung";
            $patientStmt = $conn->prepare($patientSql);
            if($patientStmt){
                $patientStmt->bind_param('i', $manguoidung);
                if(!$patientStmt->execute()){
                    $saveError = 'Không thể tạo dữ liệu bệnh nhân.';
                    $errorCode = $patientStmt->errno;
                }
                $patientStmt->close();
            } else{
                $saveError = 'Không thể chuẩn bị dữ liệu bệnh nhân.';
            }
        }

        //Tạo mới hoặc cập nhật hồ sơ bệnh nhân
        if($saveError === ''){
            if(!empty($mahoso)){
                $profileSql = "UPDATE HO_SO_BENH
                               SET sobhyt = ?
                               WHERE mahoso = ? AND manguoidung = ?";
                $profileStmt = $conn->prepare($profileSql);
                if($profileStmt){
                    $profileStmt->bind_param('sii', $bhyt, $mahoso, $manguoidung);
                }
            } else{
                $profileSql = "INSERT INTO HO_SO_BENH(manguoidung, sobhyt)
                               VALUES(?, ?)";
                $profileStmt = $conn->prepare($profileSql);
                if($profileStmt){
                    $profileStmt->bind_param('is', $manguoidung, $bhyt);
                }
            }

            if(!$profileStmt){
                $saveError = 'Không thể chuẩn bị lưu hồ sơ.';
            } elseif(!$profileStmt->execute()){
                $saveError = 'Không thể lưu hồ sơ.';
                $errorCode = $profileStmt->errno;
            }

            if(isset($profileStmt) && $profileStmt){
                $profileStmt->close();
            }
        }

        if($saveError === ''){
            $conn->commit();
            $_SESSION['is_logged_in'] = true;
            header('Location: dat-kham.php');
            exit;
        } else{
            $conn->rollback();
            if($errorCode == 1062){
                $message = 'Email này đã được sử dụng cho tài khoản khác.';
            } else{
                $message = $saveError . ' Vui lòng thử lại.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo hồ sơ khám bệnh</title>
    <link rel="stylesheet" href="assets/css/auth.css?v=3">
    <link rel="stylesheet" href="assets/css/footer.css?v=1">
    <script src="assets/js/auth.js" defer></script>
</head>
<body class="profile-page">
    <?php require __DIR__ . '/include/auth-header.php'; ?>

    <main class="auth-layout">
        <?php require __DIR__ . '/include/auth-story.php'; ?>
        <section class="auth-panel" aria-labelledby="auth-title">
        <div class="auth-content">
        <h1 id="auth-title">Hoàn thiện hồ sơ khám bệnh</h1>
        <p class="form-intro">Thông tin giúp cơ sở y tế chuẩn bị tốt hơn cho buổi khám của bạn.</p>

        <?php if ($message !== ''): ?>
            <div class="form-alert" role="alert">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if($userFound): ?>
        <form action="" method="post">
            <div class="field">
                <label for="hoten">Họ và tên</label>
                <input
                    type="text"
                    id="hoten"
                    value="<?php echo htmlspecialchars($hoten, ENT_QUOTES, 'UTF-8'); ?>"
                    readonly
                >
                <p class="field-note">Họ tên được lấy từ thông tin tài khoản.</p>
            </div>

            <div class="field">
                <label for="ngaysinh">Ngày sinh</label>
                <input
                    type="date"
                    id="ngaysinh"
                    name="ngaysinh"
                    value="<?php echo htmlspecialchars($ngaysinh, ENT_QUOTES, 'UTF-8'); ?>"
                    autocomplete="bday"
                    required
                >
            </div>

            <div class="field gender-field">
                <span class="field-label">Giới tính</span>
                <div class="gender-options">
                    <label class="radio-option" for="gioitinh_nam">
                        <input
                            type="radio"
                            id="gioitinh_nam"
                            name="gioitinh"
                            value="nam"
                            <?php echo $gioitinh === 'nam' ? 'checked' : ''; ?>
                            required
                        >
                        <span>Nam</span>
                    </label>
                    <label class="radio-option" for="gioitinh_nu">
                        <input
                            type="radio"
                            id="gioitinh_nu"
                            name="gioitinh"
                            value="nu"
                            <?php echo $gioitinh === 'nu' ? 'checked' : ''; ?>
                        >
                        <span>Nữ</span>
                    </label>
                </div>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                    autocomplete="email"
                    placeholder="Nhập email của bạn"
                >
            </div>

            <div class="field">
                <label for="diachi">Địa chỉ</label>
                <input
                    type="text"
                    id="diachi"
                    name="diachi"
                    value="<?php echo htmlspecialchars($diachi, ENT_QUOTES, 'UTF-8'); ?>"
                    maxlength="255"
                    autocomplete="street-address"
                    placeholder="Nhập địa chỉ hiện tại"
                    required
                >
            </div>

            <div class="field">
                <label for="bhyt">Số bảo hiểm y tế (BHYT)</label>
                <input
                    type="text"
                    id="bhyt"
                    name="bhyt"
                    value="<?php echo htmlspecialchars($bhyt, ENT_QUOTES, 'UTF-8'); ?>"
                    maxlength="20"
                    autocomplete="off"
                    placeholder="Nhập số BHYT của bạn"
                    required
                >
            </div>

            <button class="primary-button" type="submit">Lưu hồ sơ</button>
            <p class="form-note">Bạn có thể cập nhật các thông tin này bất cứ lúc nào.</p>
        </form>
        <?php endif; ?>
        </div>
        </section>
    </main>

    <?php require __DIR__ . '/include/footer.php'; ?>
</body>
</html>
