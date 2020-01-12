(function () {
    // Autosubmit the locale form
    const locale_form = document.getElementById('form-locale');
    if (locale_form) {
        locale_form.querySelector('#locale').addEventListener('change', function(e) {
            locale_form.submit();
        });
    }
}());
