import { TIDs } from 'cypress/tids';
import { TypeOpeningHours } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { formatAccessibleTime } from 'utils/accessibility/formatAccessibleTime';

export default function OpeningHoursToday({ openingHours }: { openingHours: TypeOpeningHours }) {
    const { t, lang } = useTranslation();

    const todayOpeningDayRanges = openingHours.openingHoursOfDays[0].openingHoursRanges;

    if (todayOpeningDayRanges.length === 0) {
        return null;
    }

    const ariaLabel = `${t('Today opening hours')} ${todayOpeningDayRanges
        .map(({ openingTime, closingTime }) => {
            const openingFormatted = formatAccessibleTime(openingTime, lang);
            const closingFormatted = formatAccessibleTime(closingTime, lang);

            return `${openingFormatted} ${t('to')} ${closingFormatted}`;
        })
        .join(', ')}`;

    return (
        <div aria-current="date" aria-label={ariaLabel} className="ml-2.5 text-xs" data-tid={TIDs.store_opening_hours}>
            {todayOpeningDayRanges.map(({ openingTime, closingTime }, index) => {
                const openingFormatted = formatAccessibleTime(openingTime, lang);
                const closingFormatted = formatAccessibleTime(closingTime, lang);

                return (
                    <span key={index} aria-hidden="true">
                        {index > 0 && ','} {openingFormatted} - {closingFormatted}
                    </span>
                );
            })}
        </div>
    );
}
