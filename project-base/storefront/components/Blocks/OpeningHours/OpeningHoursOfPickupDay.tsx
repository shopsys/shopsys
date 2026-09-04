import { TIDs } from 'cypress/tids';
import { formatAccessibleTime } from 'utils/accessibility/formatAccessibleTime';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { OpeningHoursOfDay } from 'utils/openingHours/openingHoursOfDay';
import { twMergeCustom } from 'utils/twMerge';

type OpeningHoursOfPickupDayProps = {
    openingHoursOfPickupDay: OpeningHoursOfDay;
    className?: string;
};

export const OpeningHoursOfPickupDay: FC<OpeningHoursOfPickupDayProps> = ({ openingHoursOfPickupDay, className }) => {
    const { t, lang } = useTranslation();

    if (openingHoursOfPickupDay.openingHoursRanges.length === 0) {
        return null;
    }

    const ariaLabel = `${t('Pickup day opening hours', { ns: 'accessibility' })} ${openingHoursOfPickupDay.openingHoursRanges
        .map(({ openingTime, closingTime }) => {
            const openingFormatted = formatAccessibleTime(openingTime, lang);
            const closingFormatted = formatAccessibleTime(closingTime, lang);

            return `${openingFormatted} ${t('to')} ${closingFormatted}`;
        })
        .join(', ')}`;

    return (
        <div aria-label={ariaLabel} className={twMergeCustom('text-xs', className)} data-tid={TIDs.store_opening_hours}>
            {openingHoursOfPickupDay.openingHoursRanges.map(({ openingTime, closingTime }, index) => {
                const openingFormatted = formatAccessibleTime(openingTime, lang);
                const closingFormatted = formatAccessibleTime(closingTime, lang);

                return (
                    <span key={`${openingTime}-${closingTime}`} aria-hidden="true">
                        {index > 0 && ','} {openingFormatted} - {closingFormatted}
                    </span>
                );
            })}
        </div>
    );
};
