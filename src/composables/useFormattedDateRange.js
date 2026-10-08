import { computed, unref } from 'vue';

/**
 * Formatiert zwei Timestamps in eine lesbare Datumsspanne (z. B. "14. Okt 2026, 10:00 – 11:30 Uhr"
 * oder "14. – 16. Okt 2026").
 *
 * @param {MaybeRef<number|string|Date>} start - Start-Zeitstempel (Sekunden, Millisekunden, String oder Date)
 * @param {MaybeRef<number|string|Date>} end - End-Zeitstempel
 * @param {Object} [options] - Optionale Konfiguration
 * @param {boolean} [options.includeTime=true] - Ob Uhrzeiten enthalten sein sollen
 * @param {string} [options.locale='de-DE'] - Locale für die Formatierung
 */
export function useFormattedDateRange(start, end, options = {}) {
    const { includeTime = true, locale = 'de-DE' } = options;

    const normalizeDate = (val) => {
        const raw = unref(val);
        if (!raw) return null;
        if (raw instanceof Date) return raw;

        const num = Number(raw);
        if (!isNaN(num)) {
            return new Date(num < 1e11 ? num * 1000 : num);
        }

        const parsed = new Date(raw);
        return isNaN(parsed.getTime()) ? null : parsed;
    };

    const formattedRange = computed(() => {
        const startDate = normalizeDate(start);
        const endDate = normalizeDate(end);

        if (!startDate || !endDate) {
            return '';
        }

        const sameDay = startDate.toDateString() === endDate.toDateString();
        const sameYear = startDate.getFullYear() === endDate.getFullYear();

        const dateOptions = {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        };

        const timeOptions = {
            hour: '2-digit',
            minute: '2-digit',
        };

        const dateFormatter = new Intl.DateTimeFormat(locale, dateOptions);
        const timeFormatter = new Intl.DateTimeFormat(locale, timeOptions);

        if (sameDay) {
            const dateStr = dateFormatter.format(startDate);
            if (!includeTime) return dateStr;

            const startTime = timeFormatter.format(startDate);
            const endTime = timeFormatter.format(endDate);
            return `${dateStr}, ${startTime} – ${endTime} Uhr`;
        }

        if (!includeTime) {
            return `${dateFormatter.format(startDate)} – ${dateFormatter.format(endDate)}`;
        }

        const startStr = `${dateFormatter.format(startDate)}, ${timeFormatter.format(startDate)} Uhr`;
        const endStr = `${dateFormatter.format(endDate)}, ${timeFormatter.format(endDate)} Uhr`;

        return `${startStr} – ${endStr}`;
    });

    return {
        formattedRange,
    };
}
