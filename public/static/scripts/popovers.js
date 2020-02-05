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

export default function () {
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
}
