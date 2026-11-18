<?php

$message = $viewData['message'] ?? '';
$messageType = $viewData['messageType'] ?? '';
$malichlamviec = $viewData['malichlamviec'] ?? null;
$slot = $viewData['slot'] ?? null;
$patient = $viewData['patient'] ?? null;
$hasPatientProfile = !empty($viewData['hasPatientProfile']);
$isLoggedIn = !empty($viewData['isLoggedIn']);
$lydo = $viewData['lydo'] ?? '';
$bookingCompleted = !empty($viewData['bookingCompleted']);

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$formatDate = static function ($date) {
    $timestamp = strtotime($date);
    return $timestamp ? date('d/m/Y', $timestamp) : $date;
};

$formatTime = static function ($datetime) {
    $timestamp = strtotime($datetime);
    return $timestamp ? date('H:i', $timestamp) : $datetime;
};

$formatGender = static function ($gender) {
    return $gender === 'nam' ? 'Nam' : ($gender === 'nu' ? 'Nữ' : 'Chưa cập nhật');
};

$doctorInitial = function_exists('mb_substr')
    ? mb_strtoupper(mb_substr((string) ($slot['tenbacsi'] ?? ''), 0, 1, 'UTF-8'), 'UTF-8')
    : strtoupper(substr((string) ($slot['tenbacsi'] ?? ''), 0, 1));

$backUrl = $viewData['backUrl'] ?? 'dat-kham.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Xem thông tin bác sĩ và xác nhận lịch khám.">
    <title>Chi tiết lịch khám</title>
    <link rel="stylesheet" href="assets/css/appointment.css?v=7">
    <link rel="stylesheet" href="assets/css/appointment-detail.css?v=2">
    <link rel="stylesheet" href="assets/css/nav-menu.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/footer.css?v=4">
    <script src="assets/js/ajax.js?v=3" defer></script>
</head>

<body class="appointment-page appointment-detail-page">
    <header class="appointment-header">
        <a class="appointment-brand" href="index.php" aria-label="Đặt lịch khám — Trang chủ">
            <img class="appointment-brand-logo" src="assets/images/logo.png?v=2" width="28" height="28"
                alt="Logo cổng đặt lịch khám">
            <span>
                <span class="appointment-brand-name">Đặt lịch khám</span>
                <span class="appointment-brand-description">Cổng đặt lịch khám bệnh trực tuyến</span>
            </span>
        </a>

        <div class="appointment-nav">
            <?php require __DIR__ . '/../partials/menu.php'; ?>
        </div>
    </header>

    <main class="detail-main">
        <div class="detail-breadcrumb">
            <a href="<?php echo $escape($backUrl); ?>">Tìm lịch khám</a>
            <span aria-hidden="true">/</span>
            <span>Chi tiết lịch khám</span>
        </div>

        <?php if ($message !== '' && ($slot === null || $messageType === 'error')): ?>
        <div class="detail-alert detail-alert-<?php echo $escape($messageType ?: 'error'); ?>" role="alert">
            <?php echo $escape($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($slot === null): ?>
        <section class="detail-unavailable" aria-labelledby="unavailable-title">
            <h1 id="unavailable-title">Không thể xem khung giờ này</h1>
            <?php if ($message === ''): ?>
            <p>Khung giờ không tồn tại hoặc không còn trống.</p>
            <?php else: ?>
            <p><?php echo $escape($message); ?></p>
            <?php endif; ?>
            <a class="detail-secondary-button" href="<?php echo $escape($backUrl); ?>">Quay lại tìm lịch</a>
        </section>
        <?php else: ?>
        <div class="detail-heading">
            <p class="detail-eyebrow">Thông tin trước khi đặt</p>
            <h1>Chi tiết lịch khám</h1>
            <p>Kiểm tra thông tin bác sĩ và hồ sơ của bạn trước khi xác nhận.</p>
        </div>

        <div class="detail-layout">
            <section class="doctor-panel" aria-labelledby="doctor-title">
                <div class="panel-kicker">Bác sĩ phụ trách</div>
                <div class="doctor-heading">
                    <div class="doctor-avatar" aria-hidden="true">
                        <?php echo $escape($doctorInitial); ?>
                    </div>
                    <div>
                        <h2 id="doctor-title"><?php echo $escape($slot['tenbacsi']); ?></h2>
                        <p><?php echo $escape($slot['trinhdo'] ?: 'Bác sĩ'); ?></p>
                    </div>
                </div>

                <div class="doctor-section">
                    <h3>Chuyên khoa</h3>
                    <p class="specialty-chip"><?php echo $escape($slot['chuyenkhoa']); ?></p>
                </div>

                <div class="doctor-section doctor-introduction">
                    <h3>Giới thiệu</h3>
                    <?php if (trim((string) ($slot['gioithieu'] ?? '')) !== ''): ?>
                    <p><?php echo nl2br($escape($slot['gioithieu'])); ?></p>
                    <?php else: ?>
                    <p class="muted-text">Thông tin giới thiệu của bác sĩ chưa được cập nhật.</p>
                    <?php endif; ?>
                </div>

                <div class="schedule-summary">
                    <h3>Lịch khám đã chọn</h3>
                    <div class="schedule-row">
                        <span>Ngày khám</span>
                        <strong><?php echo $escape($formatDate($slot['thoigianbatdau'])); ?></strong>
                    </div>
                    <div class="schedule-row">
                        <span>Thời gian</span>
                        <strong>
                            <?php echo $escape($formatTime($slot['thoigianbatdau'])); ?>
                            – <?php echo $escape($formatTime($slot['thoigianketthuc'])); ?>
                        </strong>
                    </div>
                </div>
            </section>

            <section class="booking-panel" aria-labelledby="booking-title">
                <div class="panel-kicker">Xác nhận thông tin</div>
                <h2 id="booking-title">Đặt lịch khám</h2>

                <div id="booking-body">
                    <?php if ($message !== '' && $messageType === 'success'): ?>
                    <div class="detail-alert detail-alert-success" role="status">
                        <?php echo $escape($message); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!$isLoggedIn): ?>
                    <div class="login-prompt">
                        <h3>Đăng nhập để tiếp tục</h3>
                        <p>Vui lòng đăng nhập để xem hồ sơ bệnh nhân và gửi yêu cầu đặt lịch khám.</p>
                        <a class="detail-primary-button" href="dang-nhap.php">Đăng nhập để đặt lịch</a>
                    </div>
                    <?php elseif ($bookingCompleted): ?>
                    <div class="completed-booking">
                        <p>Lịch khám của bạn đã được ghi nhận và đang chờ cơ sở y tế xác nhận.</p>
                        <div class="completed-actions">
                            <a class="detail-primary-button" href="xem-lich-kham.php">Xem lịch đã đặt</a>
                            <a class="detail-secondary-button" href="<?php echo $escape($backUrl); ?>">Tìm lịch khác</a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="patient-profile" aria-labelledby="patient-title">
                        <h3 id="patient-title">Hồ sơ bệnh nhân</h3>
                        <?php if ($patient !== null): ?>
                        <dl class="patient-fields">
                            <div>
                                <dt>Họ và tên</dt>
                                <dd><?php echo $escape($patient['hoten']); ?></dd>
                            </div>
                            <div>
                                <dt>Ngày sinh</dt>
                                <dd><?php echo $escape($patient['ngaysinh'] ? $formatDate($patient['ngaysinh']) : 'Chưa cập nhật'); ?></dd>
                            </div>
                            <div>
                                <dt>Giới tính</dt>
                                <dd><?php echo $escape($formatGender($patient['gioitinh'] ?? '')); ?></dd>
                            </div>
                            <div>
                                <dt>Số điện thoại</dt>
                                <dd><?php echo $escape($patient['sdt']); ?></dd>
                            </div>
                            <div>
                                <dt>Email</dt>
                                <dd><?php echo $escape($patient['email'] ?: 'Chưa cập nhật'); ?></dd>
                            </div>
                            <div>
                                <dt>Số BHYT</dt>
                                <dd><?php echo $escape($patient['bhyt'] ?: 'Chưa cập nhật'); ?></dd>
                            </div>
                            <div class="patient-field-wide">
                                <dt>Địa chỉ</dt>
                                <dd><?php echo $escape($patient['diachi'] ?: 'Chưa cập nhật'); ?></dd>
                            </div>
                        </dl>
                        <?php endif; ?>
                    </div>

                    <?php if (!$hasPatientProfile): ?>
                    <div class="profile-warning" role="note">
                        <strong>Hồ sơ chưa hoàn thiện</strong>
                        <p>Bạn cần bổ sung ngày sinh, giới tính, địa chỉ và số BHYT trước khi đặt lịch.</p>
                        <a href="tao-ho-so.php">Hoàn thiện hồ sơ</a>
                    </div>
                    <?php else: ?>
                    <form class="detail-booking-form" method="post" action="chi-tiet-lich-kham.php"
                        data-ajax="dat_lich">
                        <?php echo csrf_field('dat_lich'); ?>
                        <input type="hidden" name="hanh_dong" value="dat_lich">
                        <input type="hidden" name="malichlamviec" value="<?php echo $escape($malichlamviec); ?>">
                        <div class="reason-field">
                            <label for="lydo">Lý do khám <span>(tùy chọn)</span></label>
                            <textarea id="lydo" name="lydo" maxlength="255"><?php echo $escape($lydo); ?></textarea>
                        </div>
                        <button class="detail-primary-button detail-submit-button" type="submit">Xác nhận đặt lịch</button>
                    </form>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
        <?php endif; ?>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
