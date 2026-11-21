'use strict';

//Cập nhật từng phần trang qua AJAX cho 4 luồng: lọc khung giờ, huỷ lịch,
//đặt lịch, lưu hồ sơ. Mỗi form chỉ tham gia khi server đặt data-ajax lên form
//— nhờ vậy form dùng chung (partial ho-so-form) vẫn submit thường ở trang khác.
//Không có JS thì các form này vẫn chạy theo luồng POST/303 cũ.

(function () {
    function gui(url, options) {
        var opts = options || {};
        var headers = opts.headers || {};
        headers['X-Requested-With'] = 'XMLHttpRequest';
        opts.headers = headers;

        return fetch(url, opts).then(function (response) {
            var contentType = response.headers.get('content-type') || '';
            if (contentType.indexOf('application/json') === -1) {
                //Nhánh redirect bị fetch đi theo: không thể chèn HTML cả trang vào một vùng nhỏ.
                throw new Error('unexpected-response');
            }

            return response.json().then(function (data) {
                //Router trả {ok, msg, data} (401/403/419), controller bệnh nhân
                //trả {ok, message, ...}. Quy về 'message' để các handler bên dưới
                //không nhận undefined.
                if (data && data.message === undefined && typeof data.msg === 'string') {
                    data.message = data.msg;
                }

                //Hết phiên: về trang đăng nhập, giống api.js của khu bác sĩ.
                if (response.status === 401) {
                    window.location.href = 'index.php?page=dang-nhap';
                    throw new Error('unauthorized');
                }

                return data;
            });
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

    // --- 1. Lọc khung giờ (page=dat-kham) -------------------------------------
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
                if (value !== '' && key !== 'page') {
                    parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
                }
            });
            return 'index.php?page=dat-kham&' + parts.join('&');
        }

        function tai(url) {
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

        //Không đẩy bộ lọc vào history: thanh địa chỉ luôn là index.php?page=dat-kham.
        //Đổi lại, F5/Back không giữ bộ lọc.
        function guiForm() {
            tai(urlTuForm());
        }

        //Chỉ tải kết quả khi người dùng bấm "Tìm lịch trống"; đổi ngày/chuyên khoa/
        //bác sĩ trong form không tự gửi yêu cầu.
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            guiForm();
        });
    }

    // --- 2. Huỷ lịch (page=lich-su-dat-kham) -------------------------------------
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

    // --- 3. Đặt lịch (page=chi-tiet-lich-kham) --------------------------------
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

    // --- 4. Lưu hồ sơ (page=thong-tin-tai-khoan) ------------------------------
    function initProfileSave() {
        //Bắt submit ở <main>: sau khi lưu, thẻ hồ sơ (gồm cả form) bị thay bằng outerHTML,
        //nên gắn trực tiếp vào form chỉ chạy được lần đầu.
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
                        //Token cũ hết hạn (vd. vừa đổi mật khẩu ở tab khác): nhận token mới,
                        //giữ nguyên dữ liệu đã nhập để người dùng bấm lưu lại.
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
                    //Form cũ có thể đã bị thay; datDangTai trên node đã tách khỏi DOM là vô hại.
                    datDangTai(form, false);
                });
        });
    }

    initSlotSearch();
    initCancel();
    initBooking();
    initProfileSave();
})();
