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

export {
    DateInterval,
    isToday,
    getWeekNumber,
};
