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

    function DateInterval(start, end) {
        this.start = start;
        this.end = end;
    }

    DateInterval.prototype.forEach = function (callback, period = 'day') {
        function isBeforeOrEqual (date1, date2) {
            if (date1.getFullYear() > date2.getFullYear()) {
                return false;
            }

            if (date1.getFullYear() < date2.getFullYear()) {
                return true;
            }

            if (period === 'month') {
                return date1.getMonth() <= date2.getMonth();
            } else {
                return date1.getMonth() < date2.getMonth() || (
                    date1.getDate() <= date2.getDate() &&
                    date1.getMonth() === date2.getMonth()
                );
            }
        }

        const iterator = new Date(this.start);
        while (isBeforeOrEqual(iterator, this.end)) {
            callback(iterator);

            if (period === 'month') {
                iterator.setMonth(iterator.getMonth() + 1);
            } else {
                iterator.setDate(iterator.getDate() + 1);
            }
        }
    };

    function isToday(date) {
        const today = new Date();
        return (
            today.getDate() === date.getDate() &&
            today.getMonth() === date.getMonth() &&
            today.getFullYear() === date.getFullYear()
        );
    }

    function getWeekNumber(date) {
        // See https://stackoverflow.com/a/6117889
        // By looking at the comments, I'm not sure the code is perfect, but
        // it's the best version I found and it gives at least a good
        // approximation. Also, it’ll be good enough while I'm not displaying
        // the week number to users. I didn't think that calculating week
        // numbers would be so complicated and I won't spend more time on it.

        date = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
        // Set to nearest Thursday: current date + 4 - current day number
        // Make Sunday's day number 7
        date.setUTCDate(date.getUTCDate() + 4 - (date.getUTCDay() || 7));
        // Get first day of year
        const yearStart = new Date(Date.UTC(date.getUTCFullYear(), 0, 1));
        // Calculate full weeks to nearest Thursday
        const weekNumber = Math.ceil((((date - yearStart) / 86400000) + 1) / 7);

        return weekNumber;
    }

    // Init calendars
    function initVue() {
        if (!window.Vue) {
            return setTimeout(initVue, 10);
        }

        Vue.component('ly-month-calendar', {
            props: {
                month: Number,
                year: Number,
                firstDay: String,
                weeksHighlights: Array,
            },

            computed: {
                firstWeekDate() {
                    // We want the first day in the calendar. Problem: it can
                    // differ from the first day of the month since the first
                    // day of the first week can be at the end of the previous
                    // month. We must calculate which day of the week we are,
                    // and substract enough days to get the first day of the
                    // week (Monday or Sunday, depending on the firstDay prop).
                    const firstWeekDate = new Date(this.year, this.month, 1);
                    const day = firstWeekDate.getDay();
                    if (day !== this.numericalFirstDay) {
                        let daysToSubstract;
                        if (this.firstDay === 'sunday') {
                            // JavaScript considers first day of the week to be
                            // Sunday (day = 0). If firstDay prop is Sunday, we
                            // don't have to do much than substracting the day
                            // to current date.
                            daysToSubstract = day;
                        } else if (day === 0) {
                            // But if firstDay prop is Monday, we consider that
                            // Sunday index is after Saturday. We still need to
                            // substract 1 because of the offset due to
                            // JavaScript considering first day being Sunday.
                            daysToSubstract = 7 - 1;
                        } else {
                            // Same here, but other days are indexed in the
                            // correct order.
                            daysToSubstract = day - 1;
                        }

                        firstWeekDate.setDate(firstWeekDate.getDate() - daysToSubstract);
                    }
                    return firstWeekDate;
                },

                lastWeekDate() {
                    const lastWeekDate = new Date(this.year, this.month + 1, 0);
                    const day = lastWeekDate.getDay();
                    if (day !== this.numericalLastDay) {
                        let daysToAdd;
                        if (this.firstDay === 'sunday') {
                            daysToAdd = this.numericalLastDay - day;
                        } else {
                            daysToAdd = 7 - day;
                        }

                        lastWeekDate.setDate(lastWeekDate.getDate() + daysToAdd);
                    }
                    return lastWeekDate;
                },

                numericalFirstDay() {
                    return this.firstDay === 'monday' ? 1 : 0;
                },

                numericalLastDay() {
                    return this.firstDay === 'monday' ? 0 : 6;
                },

                monthLabel() {
                    return configuration.l10n.months[this.month];
                },

                weekDaysLabels() {
                    let days = configuration.l10n.weekDays.slice();
                    if (this.firstDay === 'sunday') {
                        // the days list is always generated with "monday" as
                        // first value
                        const sunday = days.pop();
                        days.unshift(sunday);
                    }
                    return days;
                },

                weeks() {
                    const interval = new DateInterval(this.firstWeekDate, this.lastWeekDate);
                    let weeks = [];
                    let week;
                    interval.forEach((date) => {
                        if (date.getDay() === this.numericalFirstDay) {
                            const weekNumber = getWeekNumber(date);
                            week = {
                                number: weekNumber,
                                days: [],
                                isHighlighted: this.isHighlighted(weekNumber),
                                isFirstHighlighted: this.isFirstHighlighted(weekNumber),
                                isLastHighlighted: this.isLastHighlighted(weekNumber),
                            }
                            weeks.push(week);
                        }

                        const day = date.getDate();
                        week.days.push({
                            label: day < 10 ? '0' + day : day,
                            today: isToday(date),
                            notInMonth: date.getMonth() !== this.month,
                        });
                    });

                    return weeks;
                },
            },

            methods: {
                isHighlighted(weekNumber) {
                    if (!this.weeksHighlights) {
                        return false;
                    }
                    return this.weeksHighlights.includes(weekNumber);
                },

                isFirstHighlighted(weekNumber) {
                    if (!this.weeksHighlights) {
                        return false;
                    }
                    return this.weeksHighlights[0] === weekNumber;
                },

                isLastHighlighted(weekNumber) {
                    if (!this.weeksHighlights) {
                        return false;
                    }
                    const highlightsLength = this.weeksHighlights.length;
                    const lastWeekNumber = this.weeksHighlights[highlightsLength - 1];
                    return lastWeekNumber === weekNumber;
                },
            },

            template: `
                <div class="month-calendar">
                    <div class="month-calendar-label">
                        {{ monthLabel }}
                    </div>

                    <div class="month-calendar-container">
                        <div class="month-calendar-week-header">
                            <div
                                v-for="weekDayLabel in weekDaysLabels"
                                :key="weekDayLabel"
                            >
                                {{ weekDayLabel }}
                            </div>
                        </div>

                        <div
                            v-for="week in weeks"
                            :key="week.number"
                            :class="['month-calendar-week', {
                                'highlight': week.isHighlighted,
                                'first-highlight': week.isFirstHighlighted,
                                'last-highlight': week.isLastHighlighted,
                            }]"
                        >
                            <div
                                v-for="day in week.days"
                                :key="day.label"
                                :class="['month-calendar-day', {
                                    'not-in-month': day.notInMonth,
                                    'today': day.today,
                                }]"
                            >
                                {{ day.label }}
                            </div>
                        </div>
                    </div>
                </div>
            `,
        });

        Vue.component('ly-cycle-calendar', {
            props: {
                startAt: String,
                firstDay: String,
                workWeeks: Number,
                restWeeks: Number,
            },

            computed: {
                startDate() {
                    return new Date(this.startAt);
                },

                endDate() {
                    const daysInterval = (this.workWeeks + this.restWeeks) * 7 - 1;
                    const endAt = new Date(this.startAt);
                    endAt.setDate(endAt.getDate() + daysInterval);
                    return endAt;
                },

                calendars() {
                    const calendars = [];
                    const interval = new DateInterval(this.startDate, this.endDate);
                    interval.forEach(function (date) {
                        calendars.push({
                            month: date.getMonth(),
                            year: date.getFullYear(),
                        });
                    }, 'month');

                    return calendars;
                },

                weeksHighlight() {
                    let weekNumber = getWeekNumber(this.startDate);
                    let highlight = [];
                    for (let i = 0; i < this.workWeeks + this.restWeeks ; i++) {
                        highlight.push(weekNumber++);
                    }
                    return highlight;
                },
            },

            template: `
                <div class="cycle-calendars columns columns-border columns-center">
                    <div
                        v-for="calendar in calendars"
                        :key="calendar.year + calendar.month"
                        class="column"
                    >
                        <ly-month-calendar
                            :month="calendar.month"
                            :year="calendar.year"
                            :first-day="firstDay"
                            :weeks-highlights="weeksHighlight"
                        ></ly-month-calendar>
                    </div>
                </div>
            `,
        });

        new Vue({
            el: 'ly-cycle-calendar',
        });
    }

    initVue();
}());
