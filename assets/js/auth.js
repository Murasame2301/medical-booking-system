'use strict';

document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
    button.hidden = false;
    button.addEventListener('click', function () {
        var input = document.getElementById(button.getAttribute('aria-controls'));
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(show));
        button.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
    });
});

var password = document.getElementById('mat_khau');
var confirmation = document.getElementById('nhap_lai_mat_khau');
if (password && confirmation) {
    var checkPasswords = function () {
        confirmation.setCustomValidity(confirmation.value && confirmation.value !== password.value
            ? 'Mật khẩu nhập lại chưa khớp. Vui lòng kiểm tra lại.' : '');
    };
    password.addEventListener('input', checkPasswords);
    confirmation.addEventListener('input', checkPasswords);
}
