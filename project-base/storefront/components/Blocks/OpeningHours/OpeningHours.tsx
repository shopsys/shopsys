import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { TIDs } from 'cypress/tids';
import { TypeOpeningHours } from 'graphql/types';
import { Fragment } from 'react';
import { formatAccessibleTime } from 'utils/accessibility/formatAccessibleTime';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getIsoDayOfWeek } from 'utils/openingHours/openingHoursOfDay';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { twMergeCustom } from 'utils/twMerge';

export const OpeningHours: FC<{
    openingHours: StoreOrPacketeryPoint['openingHours'] | TypeOpeningHours;
    className?: string;
    variant?: 'default' | 'compact';
    /**
     * When set (even to null), the days are shown as a standard Monday-Sunday week without dates
     * and the day of the pickup is highlighted instead of today
     */
    pickupDate?: Date | null;
}> = ({ openingHours, className, variant = 'default', pickupDate }) => {
    const { t, lang } = useTranslation();
    const { formatDate } = useFormatDate();
    const isStandardWeek = pickupDate !== undefined;
    const highlightedDayOfWeek = isStandardWeek
        ? pickupDate !== null
            ? getIsoDayOfWeek(pickupDate)
            : null
        : openingHours.dayOfWeek;
    const openingHoursOfDays = isStandardWeek
        ? [...openingHours.openingHoursOfDays].sort((firstDay, secondDay) => firstDay.dayOfWeek - secondDay.dayOfWeek)
        : openingHours.openingHoursOfDays;

    const dayNames = [
        t('Monday'),
        t('Tuesday'),
        t('Wednesday'),
        t('Thursday'),
        t('Friday'),
        t('Saturday'),
        t('Sunday'),
    ];

    const getDayName = (currentDayOfWeek: number, requestedDayOfWeek: number): string => {
        const dayName = dayNames[requestedDayOfWeek - 1];

        if (isStandardWeek) {
            return dayName;
        }

        switch (requestedDayOfWeek - currentDayOfWeek) {
            case 0:
                return t('Today');
            case 1:
                return t('Tomorrow');
            default:
                return dayName;
        }
    };

    const formatExceptionDayText = (exceptionDay: {
        from: string;
        to?: string | null;
        times: { open: string; close: string }[];
    }): string => {
        const dateRange = exceptionDay.to
            ? `${formatDate(exceptionDay.from)} - ${formatDate(exceptionDay.to)}`
            : formatDate(exceptionDay.from);

        const timeRanges = exceptionDay.times.length
            ? exceptionDay.times.map(({ open, close }) => `${open} - ${close}`).join(', ')
            : t('Closed');

        return `${dateRange} ${timeRanges}`;
    };

    if (openingHours.openingHoursOfDays.length === 0) {
        return null;
    }

    return (
        <>
            {'exceptionDays' in openingHours &&
                openingHours.exceptionDays?.map((exceptionDay) => (
                    <div
                        key={exceptionDay.from}
                        className={twMergeCustom('px-3 pt-0 pb-3 text-text-error text-xs', className)}
                    >
                        {formatExceptionDayText(exceptionDay)}
                    </div>
                ))}

            <ul
                aria-label={t('Opening hours', { ns: 'accessibility' })}
                className={twMergeCustom('flex flex-col gap-1 self-baseline text-text-default text-xs', className)}
                data-tid={TIDs.opening_hours}
            >
                {openingHoursOfDays.map(({ date, dayOfWeek, openingHoursRanges }) => {
                    const isHighlighted = highlightedDayOfWeek === dayOfWeek;
                    const isToday = !isStandardWeek && isHighlighted;
                    const isClosedWholeDay = openingHoursRanges.length === 0;
                    const isCompact = variant === 'compact';
                    const dayLabel = isStandardWeek
                        ? getDayName(openingHours.dayOfWeek, dayOfWeek)
                        : `${getDayName(openingHours.dayOfWeek, dayOfWeek)} ${formatDate(date)}`;

                    const ariaClosedText = `${dayLabel}, ${t('Closed')}`;
                    const ariaOpenText = `${dayLabel}, ${t('Open')} ${openingHoursRanges
                        .map(({ openingTime, closingTime }) => {
                            const openingFormatted = formatAccessibleTime(openingTime, lang);
                            const closingFormatted = formatAccessibleTime(closingTime, lang);

                            return `${openingFormatted} ${t('to')} ${closingFormatted}`;
                        })
                        .join(', ')}`;

                    const dayAriaText = isClosedWholeDay ? ariaClosedText : ariaOpenText;

                    return (
                        <li
                            key={dayOfWeek}
                            aria-current={isToday ? 'date' : undefined}
                            aria-label={dayAriaText}
                            className={twMergeCustom(
                                'flex list-none flex-col flex-wrap gap-x-5 gap-y-2 rounded-lg sm:flex-row sm:items-center',
                                isCompact ? 'px-3 py-2' : 'p-2',
                                isHighlighted && !isCompact && 'bg-background-accent-less',
                                isHighlighted && isCompact && 'bg-background-default',
                                !isHighlighted && 'hover:bg-background-more',
                            )}
                        >
                            <span
                                aria-hidden="true"
                                className={twMergeCustom(
                                    'font-secondary font-semibold',
                                    isCompact ? 'w-36 text-xs' : 'h6 w-44',
                                )}
                            >
                                {dayLabel}
                            </span>

                            <span aria-hidden="true" className={twMergeCustom(isCompact && 'text-xs')}>
                                {isClosedWholeDay
                                    ? t('Closed')
                                    : openingHoursRanges.map(({ openingTime, closingTime }, index) => {
                                          const openingFormatted = formatAccessibleTime(openingTime, lang);
                                          const closingFormatted = formatAccessibleTime(closingTime, lang);

                                          return (
                                              <Fragment key={`${openingTime}-${closingTime}`}>
                                                  {index > 0 && ','} {openingFormatted} - {closingFormatted}
                                              </Fragment>
                                          );
                                      })}
                            </span>

                            {isToday && (
                                <OpeningStatus
                                    className={twMergeCustom('self-baseline sm:self-auto', isCompact && 'text-xs')}
                                    status={openingHours.status}
                                />
                            )}
                        </li>
                    );
                })}
            </ul>
        </>
    );
};
