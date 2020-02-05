import LyMonthCalendar from './LyMonthCalendar.js';

import {
    DateInterval,
    getWeekNumber,
} from '../utils/dates.js';

const LyCycleCalendar = {
    props: {
        startAt: String,
        firstDay: String,
        workWeeks: Number,
        restWeeks: Number,
    },

    components: {
        LyMonthCalendar,
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
};

export default LyCycleCalendar;
