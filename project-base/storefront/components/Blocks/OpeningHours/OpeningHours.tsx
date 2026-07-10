import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { TIDs } from 'cypress/tids';
import { TypeOpeningHours } from 'graphql/types';
import { Fragment } from 'react';
import { formatAccessibleTime } from 'utils/accessibility/formatAccessibleTime';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { twMergeCustom } from 'utils/twMerge';

export const OpeningHours: FC<{
    openingHours: StoreOrPacketeryPoint['openingHours'] | TypeOpeningHours;
    className?: string;
    variant?: 'default' | 'compact';
}> = ({ openingHours, className, variant = 'default' }) => {
    const { t, lang } = useTranslation();
    const { formatDate } = useFormatDate();

    const getDayName = (currentDayOfWeek: number, requestedDayOfWeek: number): string => {
        const dayNames = [
            t('Monday'),
            t('Tuesday'),
            t('Wednesday'),
            t('Thursday'),
            t('Friday'),
            t('Saturday'),
            t('Sunday'),
        ];

        const dayName = dayNames[requestedDayOfWeek - 1];

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
                {openingHours.openingHoursOfDays.map(({ date, dayOfWeek, openingHoursRanges }) => {
                    const isToday = openingHours.dayOfWeek === dayOfWeek;
                    const isClosedWholeDay = openingHoursRanges.length === 0;
                    const isCompact = variant === 'compact';

                    const ariaClosedText = `${getDayName(openingHours.dayOfWeek, dayOfWeek)} ${formatDate(date)}, ${t('Closed')}`;
                    const ariaOpenText = `${getDayName(openingHours.dayOfWeek, dayOfWeek)} ${formatDate(date)}, ${t('Open')} ${openingHoursRanges
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
                                isToday && !isCompact && 'bg-background-accent-less',
                                isToday && isCompact && 'bg-background-default',
                                !isToday && 'hover:bg-background-more',
                            )}
                        >
                            <span
                                aria-hidden="true"
                                className={twMergeCustom(
                                    'font-secondary font-semibold',
                                    isCompact ? 'w-36 text-xs' : 'h6 w-44',
                                )}
                            >
                                {getDayName(openingHours.dayOfWeek, dayOfWeek)} <span>{formatDate(date)}</span>
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
