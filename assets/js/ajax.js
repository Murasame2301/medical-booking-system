'use strict';

// Cap nhat tung phan trang qua AJAX cho 4 luong: loc khung gio, huy lich,
// dat lich, luu ho so. Moi form chi tham gia khi server dat data-ajax len form
// — nho vay form dung chung (ho-so-form.php) van submit thuong o trang khac.
// Khong co JS thi cac form nay van chay theo luong POST/303 cu.

(function () {
    function gui(url, options) {
        var opts = options || {};
        var headers = opts.headers || {};
        headers['X-Requested-With'] = 'XMLHttpRequest';
        opts.headers = headers;

        return fetch(url, opts).then(function (response) {
            var contentType = response.headers.get('content-type') || '';
            if (contentType.indexOf('application/json') === -1) {
                // Nhanh redirect bi fetch di theo: khong the chen HTML ca trang vao mot vung nho.
                throw new Error('unexpected-response');
            }
            return response.json();
        });
    }

    function taoAlert(className, message, type) {
        var div = document.createElement('div');
        div.className = className + ' ' + className + '-' + (type || 'error');
        div.setAttribute('role', type === 'success' ? 'status' : 'alert');
        div.textContent = message;
        return div;
    }

    function datDangTai(scope, dangTai) {
        if (!scope) return;
        scope.setAttribute('aria-busy', dangTai ? 'true' : 'false');
        scope.classList.toggle('is-loading', !!dangTai);
        var buttons = scope.querySelectorAll('button[type="submit"]');
        Array.prototype.forEach.call(buttons, function (button) {
            button.disabled = !!dangTai;
        });
    }

    var LOI_KET_NOI = 'Không thể kết nối. Vui lòng thử lại.';

    // --- 1. Loc khung gio (dat-kham.php) -------------------------------------
    function initSlotSearch() {
        var form = document.querySelector('[data-ajax="tim_khung_gio"]');
        if (!form) return;

        var alertBox = document.getElementById('search-alert');
        var results = document.getElementById('slot-results');
        var doctorSelect = document.getElementById('mabacsi');
        var seq = 0;

        function urlTuForm() {
            var params = new URLSearchParams(new FormData(form));
            var parts = [];
            params.forEach(function (value, key) {
                if (value !== '') {
                    parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
                }
            });
            return 'dat-kham.php?' + parts.join('&');
        }

        function tai(url, dayHistory) {
            var lan = ++seq;
            form.classList.add('is-loading');

            gui(url).then(function (data) {
                if (lan !== seq) return;

                if (alertBox) {
                    alertBox.innerHTML = data.alertHtml || '';
                }
                if (results) {
                    results.innerHTML = data.resultsHtml || '';
                }
                if (doctorSelect && typeof data.doctorOptionsHtml === 'string') {
                    doctorSelect.innerHTML = data.doctorOptionsHtml;
                    doctorSelect.value = data.mabacsi ? String(data.mabacsi) : '';
                }
                if (dayHistory && data.url && window.history && window.history.pushState) {
                    window.history.pushState({ ajax: true }, '', data.url);
                }
            }).catch(function () {
                if (lan !== seq || !alertBox) return;
                alertBox.innerHTML = '';
                alertBox.appendChild(taoAlert('appointment-alert', LOI_KET_NOI, 'error'));
            }).finally(function () {
                if (lan === seq) {
                    form.classList.remove('is-loading');
                }
            });
        }

        function guiForm(dayHistory) {
            tai(urlTuForm(), dayHistory);
        }

        // Chi tai ket qua khi nguoi dung bam "Tim lich trong"; doi ngay/chuyen khoa/
        // bac si trong form khong tu gui yeu cau.
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            guiForm(true);
        });

        window.addEventListener('popstate', function () {
            tai('dat-kham.php' + (window.location.search || ''), false);
        });
    }

    // --- 2. Huy lich (xem-lich-kham.php) -------------------------------------
    function initCancel() {
        var list = document.getElementById('history-list');
        if (!list) return;
        var tray = document.getElementById('history-alert');

        list.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form.matches || !form.matches('[data-ajax="huy_lich"]')) return;

            event.preventDefault();
            datDangTai(form, true);

            gui(form.action, { method: 'POST', body: new FormData(form) })
                .then(function (data) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                    if (data.reload) {
                        window.location.reload();
                        return;
                    }

                    if (data.ok && data.cardHtml) {
                        var card = list.querySelector(
                            '[data-malichkham="' + String(data.malichkham) + '"]'
                        );
                        if (card) {
                            card.outerHTML = data.cardHtml;
                        }
                    }

                    if (tray) {
                        tray.innerHTML = '';
                        tray.appendChild(taoAlert('history-alert', data.message, data.messageType || 'error'));
                    }
                })
                .catch(function () {
                    if (tray) {
                        tray.innerHTML = '';
                        tray.appendChild(taoAlert('history-alert', LOI_KET_NOI, 'error'));
                    }
                })
                .finally(function () {
                    datDangTai(form, false);
                });
        });
    }

    // --- 3. Dat lich (chi-tiet-lich-kham.php) --------------------------------
    function initBooking() {
        var form = document.querySelector('[data-ajax="dat_lich"]');
        if (!form) return;
        var body = document.getElementById('booking-body');

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            datDangTai(form, true);

            gui(form.action, { method: 'POST', body: new FormData(form) })
                .then(function (data) {
                    if (data.reload) {
                        window.location.reload();
                        return;
                    }
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                    if (data.panelHtml) {
                        if (body) {
                            body.innerHTML = data.panelHtml;
                        }
                        return;
                    }
                    if (!body) return;

                    var cu = body.querySelector('.detail-alert-ajax');
                    if (cu) {
                        cu.remove();
                    }
                    var alert = taoAlert('detail-alert', data.message, data.messageType || 'error');
                    alert.classList.add('detail-alert-ajax');
                    body.insertBefore(alert, body.firstChild);
                })
                .catch(function () {
                    if (!body) return;
                    var cu = body.querySelector('.detail-alert-ajax');
                    if (cu) {
                        cu.remove();
                    }
                    var alert = taoAlert('detail-alert', LOI_KET_NOI, 'error');
                    alert.classList.add('detail-alert-ajax');
                    body.insertBefore(alert, body.firstChild);
                })
                .finally(function () {
                    datDangTai(form, false);
                });
        });
    }

    // --- 4. Luu ho so (thong-tin-tai-khoan.php) ------------------------------
    function initProfileSave() {
        // Bat submit o <main>: sau khi luu, the ho so (gom ca form) bi thay bang outerHTML,
        // nen gan truc tiep vao form chi chay duoc lan dau.
        var root = document.querySelector('.account-main');
        if (!root || !root.querySelector('[data-ajax="cap_nhat_ho_so"]')) return;
        var tabs = document.querySelector('.account-tabs');

        function hienLoi(form, message) {
            var cu = form.querySelector('.ho-so-alert');
            if (cu) {
                cu.remove();
            }
            var alert = taoAlert('ho-so-alert', message, 'error');
            form.insertBefore(alert, form.firstChild);
        }

        function hienThanhCong(message) {
            if (!tabs || !tabs.parentNode) return;
            var cu = document.querySelector('.account-alert-ajax');
            if (cu) {
                cu.remove();
            }
            var alert = taoAlert('account-alert', message, 'success');
            alert.classList.add('account-alert-ajax');
            tabs.parentNode.insertBefore(alert, tabs.nextSibling);
        }

        function capNhatToken(token) {
            var inputs = document.querySelectorAll('input[name="csrf_token"]');
            Array.prototype.forEach.call(inputs, function (input) {
                input.value = token;
            });
        }

        root.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form.matches || !form.matches('[data-ajax="cap_nhat_ho_so"]')) return;

            event.preventDefault();
            datDangTai(form, true);

            gui(form.action, { method: 'POST', body: new FormData(form) })
                .then(function (data) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                    if (data.csrfToken) {
                        // Token cu het han (vd. vua doi mat khau o tab khac): nhan token moi,
                        // giu nguyen du lieu da nhap de nguoi dung bam luu lai.
                        capNhatToken(data.csrfToken);
                    }
                    if (!data.ok) {
                        hienLoi(form, data.message);
                        return;
                    }

                    var card = document.getElementById('ho-so-card');
                    if (card && data.cardHtml) {
                        card.outerHTML = data.cardHtml;
                    }
                    var emailValue = document.getElementById('account-email-value');
                    if (emailValue) {
                        var email = (data.email || '').trim();
                        emailValue.textContent = email !== '' ? email : 'Chưa cập nhật';
                    }
                    hienThanhCong(data.message);
                })
                .catch(function () {
                    hienLoi(form, LOI_KET_NOI);
                })
                .finally(function () {
                    // Form cu co the da bi thay; datDangTai tren node da tach khoi DOM la vo hai.
                    datDangTai(form, false);
                });
        });
    }

    initSlotSearch();
    initCancel();
    initBooking();
    initProfileSave();
})();
