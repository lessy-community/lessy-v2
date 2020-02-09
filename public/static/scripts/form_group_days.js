function init() {
    const groups = document.querySelectorAll('.form-group-days');
    groups.forEach(initFormGroup);
}

function initFormGroup(group) {
    const behavior = group.getAttribute('data-behavior');
    const buttons = group.querySelectorAll('button');
    buttons.forEach(function (button) {
        button.addEventListener('click', function (e) {
            if (behavior === 'radio') {
                buttons.forEach(function (button) {
                    switchButtonOff(button);
                });
            }

            toggleButton(button);
        });
    });
}

function switchButtonOff(button) {
    button.classList.replace('button-primary', 'button-ghost');
}

function switchButtonOn(button) {
    button.classList.replace('button-ghost', 'button-primary');
}

function toggleButton(button) {
    if (button.classList.contains('button-primary')) {
        switchButtonOff(button);
    } else if (button.classList.contains('button-ghost')) {
        switchButtonOn(button);
    }
}

export default init;
