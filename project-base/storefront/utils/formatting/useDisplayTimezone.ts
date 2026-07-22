import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';

export const useDisplayTimezone = (): string => {
    const { fallbackTimezone } = useDomainConfig();
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });

    return settingsData?.settings?.displayTimezone || fallbackTimezone;
};
