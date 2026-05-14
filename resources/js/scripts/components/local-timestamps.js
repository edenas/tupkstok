function formatLocalTimestamp(date) {
    if (Number.isNaN(date.getTime())) {
        return null;
    }

    const formatter = new Intl.DateTimeFormat(undefined, {
        day: '2-digit',
        hour: '2-digit',
        hourCycle: 'h23',
        minute: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
    const parts = Object.fromEntries(formatter.formatToParts(date).map((part) => [part.type, part.value]));

    return `${parts.year}-${parts.month}-${parts.day} ${parts.hour}:${parts.minute}`;
}

export function initializeLocalTimestamps() {
    document.querySelectorAll('[data-local-timestamp]').forEach((element) => {
        const timestamp = element.getAttribute('data-local-timestamp');

        if (!timestamp) {
            return;
        }

        const formattedTimestamp = formatLocalTimestamp(new Date(timestamp));

        if (!formattedTimestamp) {
            return;
        }

        element.textContent = formattedTimestamp;
        element.setAttribute('title', formattedTimestamp);
    });
}
