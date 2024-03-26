import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { formatDate, formatDateAndTime } from 'helpers/formaters/formatDate';

export const useFormatDate = (): {
    formatDate: typeof formatDate;
    formatDateAndTime: typeof formatDateAndTime;
} => {
    const { fallbackTimezone } = useDomainConfig();
    const [{ data }] = useSettingsQuery({ requestPolicy: 'cache-only' });

    const timezone = data?.settings?.displayTimezone || fallbackTimezone;

    return {
        formatDate: (date, format) => formatDate(date, timezone, format),
        formatDateAndTime: (date) => formatDateAndTime(date, timezone),
    };
};
