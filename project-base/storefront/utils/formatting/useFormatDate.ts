import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { formatDate, formatDateAndTime } from 'utils/formaters/formatDate';
import { useDisplayTimezone } from 'utils/formatting/useDisplayTimezone';

export const useFormatDate = (): {
    formatDate: typeof formatDate;
    formatDateAndTime: typeof formatDateAndTime;
} => {
    const { defaultLocale } = useDomainConfig();
    const timezone = useDisplayTimezone();

    return {
        formatDate: (date) => formatDate(date, timezone, defaultLocale),
        formatDateAndTime: (date) => formatDateAndTime(date, timezone, defaultLocale),
    };
};
