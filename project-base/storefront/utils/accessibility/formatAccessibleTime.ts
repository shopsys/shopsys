import dayjs from 'dayjs';
import LocalizedFormat from 'dayjs/plugin/localizedFormat';

dayjs.extend(LocalizedFormat);

export const formatAccessibleTime = (timeString: string, locale: string = 'en'): string => {
    // Create a dayjs object with the time on a fixed date
    const timeObj = dayjs(`2000-01-01 ${timeString}`);

    const use24HourFormat = locale !== 'en';

    if (use24HourFormat) {
        return timeObj.locale(locale).format('H:mm');
    }

    // 12-hour format for English - dayjs handles AM/PM automatically
    // If minutes are 0, omit them for better accessibility (e.g., "9 AM" instead of "9:00 AM")
    if (timeObj.minute() === 0) {
        return timeObj.format('h A');
    }
    return timeObj.format('h:mm A');
};
