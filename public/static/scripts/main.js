(function () {
    // Load configuration
    const confElement = document.getElementById('javascript-configuration');
    const configuration = JSON.parse(confElement.innerHTML);

    // Autosubmit the locale form
    const locale_form = document.getElementById('form-locale');
    if (locale_form) {
        locale_form.querySelector('#locale').addEventListener('change', function(e) {
            locale_form.submit();
        });
    }

    // Automatically set timezone value in registration form
    let timezone_input = document.getElementById('timezone');
    if (timezone_input) {
        // set default timezone if input is empty
        const prefered_timezone_value = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (!timezone_input.value) {
            timezone_input.value = prefered_timezone_value;
        }

        let timezone_change_button = document.getElementById('timezone-change');
        // sync the "active" status of the button (the input might be already
        // enabled if the value is invalid and the form submitted)
        if (!timezone_input.readOnly) {
            timezone_change_button.classList.add('active');
        }

        timezone_change_button.addEventListener('click', function(e) {
            // allow to change input value by clicking on the change button
            timezone_change_button.classList.toggle('active');
            timezone_input.readOnly = !timezone_input.readOnly;

            if (!timezone_input.readOnly) {
                // set the focus on the input
                timezone_input.focus();
                timezone_input.setSelectionRange(0, timezone_input.value.length);
            } else {
                timezone_change_button.blur();
            }
        });
    }

    // Init the popovers
    let popper_instance;
    const popover_arrow = document.createElement('div');
    popover_arrow.setAttribute('data-popper-arrow', '');

    function closeCurrentPopover() {
        if (popper_instance) {
            const toggle = popper_instance.state.elements.reference;
            const popover = popper_instance.state.elements.popper;
            toggle.classList.remove('active');
            popover.removeAttribute('data-opened');
            popper_instance.destroy();
            popper_instance = null;
        }
    }

    function openPopover(toggle) {
        const popover_id = toggle.getAttribute('data-toggle-popover');
        const popover = document.getElementById(popover_id);
        popover.appendChild(popover_arrow);
        toggle.classList.add('active');
        popper_instance = Popper.createPopper(toggle, popover, {
            placement: 'bottom-end',
        });
        popover.setAttribute('data-opened', '');
    }

    const popover_toggles = document.querySelectorAll('[data-toggle-popover]');
    popover_toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (popper_instance) {
                closeCurrentPopover();
            } else {
                openPopover(toggle);
            }
        });
    });

    // Allow to close current popover by clicking anywhere on the page
    document.addEventListener('click', closeCurrentPopover);
}());
