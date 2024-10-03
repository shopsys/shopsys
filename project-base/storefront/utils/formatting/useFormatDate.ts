import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { formatDate, formatDateAndTime } from 'utils/formaters/formatDate';

export const useFormatDate = (): {
    formatDate: typeof formatDate;
    formatDateAndTime: typeof formatDateAndTime;
} => {
    const { fallbackTimezone } = useDomainConfig();
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });

    const timezone = settingsData?.settings?.displayTimezone || fallbackTimezone;

    return {
        formatDate: (date) => formatDate(date, timezone),
        formatDateAndTime: (date) => formatDateAndTime(date, timezone),
    };
};
