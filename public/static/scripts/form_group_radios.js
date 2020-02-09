function init() {
    const groups = document.querySelectorAll('.form-group-radios-group');
    groups.forEach(function (group) {
        const input = group.querySelector('input');
        input.addEventListener('change', toggleGroups);
    });
}

function toggleGroups(e) {
    const groups = document.querySelectorAll('.form-group-radios-group');
    groups.forEach(function (group) {
        const input = group.querySelector('input');
        if (input.checked) {
            group.classList.add('active');
        } else {
            group.classList.remove('active');
        }
    });
}

export default init;
