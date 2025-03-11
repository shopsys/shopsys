import { getSettingsQuery } from 'app/_queries/getSettingsQuery';
import { getDomainConfig } from 'app/_utils/getDomainConfig';
import { headers } from 'next/headers';
import { formatDate, formatDateAndTime } from 'utils/formaters/formatDate';

export async function getFormatDate(): Promise<{
    formatDate: typeof formatDate;
    formatDateAndTime: typeof formatDateAndTime;
}> {
    const { data: settingsData } = await getSettingsQuery();
    const domainConfig = getDomainConfig(headers().get('host')!);

    const timezone = settingsData?.settings?.displayTimezone || domainConfig.fallbackTimezone;

    return {
        formatDate: (date) => formatDate(date, timezone),
        formatDateAndTime: (date) => formatDateAndTime(date, timezone),
    };
}
