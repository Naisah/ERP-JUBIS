export function businessDate(value, withTime = false, timeZone = 'Asia/Manila') {
    if (!value) return '—';
    // Calendar-only fields are not instants; keep their original calendar date.
    const calendarOnly = /^\d{4}-\d{2}-\d{2}$/.test(value);
    const date = new Date(calendarOnly ? `${value}T12:00:00Z` : value);
    if (Number.isNaN(date.getTime())) return '—';
    return new Intl.DateTimeFormat('en-PH', {
        timeZone: calendarOnly ? 'UTC' : timeZone,
        year: 'numeric', month: 'short', day: 'numeric',
        ...(withTime && !calendarOnly ? { hour: 'numeric', minute: '2-digit' } : {}),
    }).format(date);
}
