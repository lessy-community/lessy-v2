import configuration from '../configuration.js';

import {
    DateInterval,
    getWeekNumber,
    isToday,
} from '../utils/dates.js';

const LyMonthCalendar = {
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
};

export default LyMonthCalendar;
