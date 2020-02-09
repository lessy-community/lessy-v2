import LyCycleCalendar from './components/LyCycleCalendar.js';

import initFormGroupsRadios from './form_group_radios.js';
import initFormGroupsDays from './form_group_days.js';
import initPopovers from './popovers.js';

initFormGroupsRadios();
initFormGroupsDays();
initPopovers();

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

// Init calendars
new Vue({
    el: 'ly-cycle-calendar',
    components: {
        LyCycleCalendar,
    }
});
