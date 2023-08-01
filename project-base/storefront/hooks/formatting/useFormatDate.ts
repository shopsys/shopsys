import { formatDate, formatDateAndTime } from 'helpers/formaters/formatDate';
import { useDomainConfig } from 'hooks/useDomainConfig';

export const useFormatDate = (): {
    formatDate: typeof formatDate;
    formatDateAndTime: typeof formatDateAndTime;
} => {
    const { timezone } = useDomainConfig();

    return {
        formatDate: (date, format) => formatDate(date, timezone, format),
        formatDateAndTime: (date) => formatDateAndTime(date, timezone),
    };
};
