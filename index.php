<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Đặt lịch khám bệnh trực tuyến nhanh chóng và thuận tiện.">
    <title>Đặt lịch khám</title>
    <link rel="stylesheet" href="assets/css/home.css">
</head>
<body class="home-page">
    <header class="home-header">
        <a class="home-brand" href="index.php" aria-label="Đặt lịch khám — Trang chủ">
            <span class="home-brand-mark" aria-hidden="true">
                <svg viewBox="0 0 28 28" fill="none">
                    <path d="M11 4h6v7h7v6h-7v7h-6v-7H4v-6h7V4Z" fill="currentColor"/>
                </svg>
            </span>
            <span>
                <span class="home-brand-name">Đặt lịch khám</span>
                <span class="home-brand-description">Cổng đặt lịch khám bệnh trực tuyến</span>
            </span>
        </a>

        <div class="home-nav">
            <?php require __DIR__ . '/include/menu.php'; ?>
        </div>
    </header>

    <main>
        <section class="home-hero" aria-labelledby="home-title">
            <div class="hero-copy">
                <p class="hero-kicker">Chăm sóc sức khỏe chủ động</p>
                <h1 id="home-title">Chủ động lịch khám.<br>An tâm chăm sóc<br>sức khỏe.</h1>
                <p class="hero-description">Đặt lịch khám và theo dõi lịch hẹn của bạn ở cùng một nơi. Bắt đầu đơn giản, yên tâm cho mỗi lần thăm khám.</p>
                <div class="hero-actions">
                    <a class="hero-button hero-button-primary" href="tim-lich-kham.php">Đặt khám ngay</a>
                    <a class="hero-button hero-button-secondary" href="tao-ho-so.php">Xem hồ sơ</a>
                </div>
                <p class="hero-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z"/>
                        <path d="m8 12 3 3 5-6"/>
                    </svg>
                    Thông tin của bạn được quản lý trong một tài khoản riêng.
                </p>
            </div>

            <div class="hero-visual" aria-label="Minh họa lịch khám đã được xác nhận" role="img">
                <div class="visual-orbit visual-orbit-one"></div>
                <div class="visual-orbit visual-orbit-two"></div>
                <svg class="calendar-art" viewBox="0 0 520 430" fill="none" aria-hidden="true">
                    <ellipse cx="268" cy="376" rx="177" ry="18" fill="#DCE6F2"/>
                    <path d="M83 252c-37-76 8-164 98-174 85-10 119 25 191 19 71-6 128 41 132 116 5 91-72 143-190 143-112 0-195-31-231-104Z" fill="#E4EDF9"/>
                    <rect x="123" y="72" width="284" height="259" rx="20" fill="#BFD5ED" transform="rotate(6 123 72)"/>
                    <rect x="102" y="68" width="284" height="259" rx="20" fill="white"/>
                    <path d="M102 88c0-11 9-20 20-20h244c11 0 20 9 20 20v53H102V88Z" fill="#1559B7"/>
                    <path d="M166 54v35M322 54v35" stroke="#153452" stroke-width="11" stroke-linecap="round"/>
                    <path d="M137 116h214" stroke="#72A0DE" stroke-width="2"/>
                    <g fill="#DCE6F2">
                        <rect x="137" y="170" width="35" height="30" rx="6"/>
                        <rect x="191" y="170" width="35" height="30" rx="6"/>
                        <rect x="245" y="170" width="35" height="30" rx="6"/>
                        <rect x="299" y="170" width="35" height="30" rx="6"/>
                        <rect x="137" y="219" width="35" height="30" rx="6"/>
                        <rect x="191" y="219" width="35" height="30" rx="6"/>
                        <rect x="299" y="219" width="35" height="30" rx="6"/>
                        <rect x="137" y="268" width="35" height="30" rx="6"/>
                        <rect x="191" y="268" width="35" height="30" rx="6"/>
                        <rect x="245" y="268" width="35" height="30" rx="6"/>
                    </g>
                    <rect x="241" y="214" width="44" height="41" rx="9" fill="#1559B7"/>
                    <path d="m253 235 7 7 14-16" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="405" cy="304" r="51" fill="#E4F5F1" stroke="white" stroke-width="8"/>
                    <path d="m384 303 14 14 27-30" stroke="#27776D" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M68 143v25M55.5 155.5h25" stroke="#6AADA5" stroke-width="5" stroke-linecap="round"/>
                    <circle cx="443" cy="120" r="8" fill="#A9C5E6"/>
                </svg>
                <div class="hero-badge hero-badge-time">
                    <span class="badge-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="8.5"/>
                            <path d="M12 7v5l3 2"/>
                        </svg>
                    </span>
                    <span><strong>Lịch hẹn rõ ràng</strong><small>Theo dõi dễ dàng</small></span>
                </div>
            </div>
        </section>

        <section class="home-benefits" aria-label="Lợi ích của cổng đặt lịch khám">
            <article class="benefit-item">
                <span class="benefit-number" aria-hidden="true">01</span>
                <div>
                    <h2>Đặt lịch thuận tiện</h2>
                    <p>Chọn lịch khám phù hợp mà không cần chờ đợi tại quầy.</p>
                </div>
            </article>
            <article class="benefit-item">
                <span class="benefit-number" aria-hidden="true">02</span>
                <div>
                    <h2>Theo dõi lịch hẹn</h2>
                    <p>Xem lại các buổi khám và trạng thái lịch hẹn của bạn.</p>
                </div>
            </article>
            <article class="benefit-item">
                <span class="benefit-number" aria-hidden="true">03</span>
                <div>
                    <h2>Hồ sơ rõ ràng</h2>
                    <p>Lưu thông tin cá nhân để việc đặt khám lần sau nhanh hơn.</p>
                </div>
            </article>
        </section>
    </main>

    <footer class="home-footer">
        <span>© Cổng đặt lịch khám bệnh trực tuyến</span>
        <span>Sẵn sàng đồng hành cùng bạn</span>
    </footer>
</body>
</html>
