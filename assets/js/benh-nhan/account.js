'use strict';

document.querySelectorAll('[data-password-target]').forEach(function (button) {
    var input = document.getElementById(button.getAttribute('data-password-target'));
    if (!input) return;
    button.hidden = false;
    button.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(show));
        button.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
    });
});

document.querySelectorAll('[data-password-confirm-for]').forEach(function (confirmation) {
    var source = document.getElementById(confirmation.getAttribute('data-password-confirm-for'));
    if (!source) return;
    var check = function () {
        confirmation.setCustomValidity(
            confirmation.value && confirmation.value !== source.value
                ? 'Mật khẩu nhập lại chưa khớp. Vui lòng kiểm tra lại.'
                : ''
        );
    };
    source.addEventListener('input', check);
    confirmation.addEventListener('input', check);
});
