import { useSettingsQueryApi } from 'graphql/generated';
import { formatDate, formatDateAndTime } from 'helpers/formaters/formatDate';
import { useDomainConfig } from 'hooks/useDomainConfig';

export const useFormatDate = (): {
    formatDate: typeof formatDate;
    formatDateAndTime: typeof formatDateAndTime;
} => {
    const { fallbackTimezone } = useDomainConfig();
    const [{ data }] = useSettingsQueryApi({ requestPolicy: 'cache-only' });

    const timezone = data?.settings?.displayTimezone || fallbackTimezone;

    return {
        formatDate: (date, format) => formatDate(date, timezone, format),
        formatDateAndTime: (date) => formatDateAndTime(date, timezone),
    };
};
