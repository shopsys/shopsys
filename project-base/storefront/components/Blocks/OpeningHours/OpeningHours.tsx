import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { TIDs } from 'cypress/tids';
import { TypeOpeningHours } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { Fragment } from 'react';
import { twJoin } from 'tailwind-merge';
import { formatAccessibleTime } from 'utils/accessibility/formatAccessibleTime';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { twMergeCustom } from 'utils/twMerge';

export const OpeningHours: FC<{ openingHours: StoreOrPacketeryPoint['openingHours'] | TypeOpeningHours }> = ({
    openingHours,
    className,
}) => {
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

    if (openingHours.openingHoursOfDays.length === 0) {
        return null;
    }

    return (
        <>
            {'exceptionDays' in openingHours &&
                openingHours.exceptionDays?.map((exceptionDay, index) => {
                    let exceptionDayText = formatDate(exceptionDay.from);

                    if (exceptionDay.to) {
                        exceptionDayText += ` - ${formatDate(exceptionDay.to)}`;
                    }

                    if (exceptionDay.times.length) {
                        for (let index = 0; index < exceptionDay.times.length; index++) {
                            if (index === 0) {
                                exceptionDayText += ` ${exceptionDay.times[index].open} - ${exceptionDay.times[index].close}`;
                            } else {
                                exceptionDayText += `, ${exceptionDay.times[index].open} - ${exceptionDay.times[index].close}`;
                            }
                        }
                    } else {
                        exceptionDayText += ` ${t('Closed')}`;
                    }

                    return (
                        <div key={index} className={twMergeCustom('text-text-error mb-1 text-xs', className)}>
                            {exceptionDayText}
                        </div>
                    );
                })}

            <div
                aria-label={t('Opening hours')}
                className={twMergeCustom('text-text-default flex flex-col gap-1 self-baseline text-xs', className)}
                data-tid={TIDs.opening_hours}
                role="list"
            >
                {openingHours.openingHoursOfDays.map(({ date, dayOfWeek, openingHoursRanges }) => {
                    const isToday = openingHours.dayOfWeek === dayOfWeek;
                    const isClosedWholeDay = openingHoursRanges.length === 0;

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
                        <div
                            key={dayOfWeek}
                            aria-current={isToday ? 'date' : undefined}
                            aria-label={dayAriaText}
                            className={twJoin(
                                'flex flex-col flex-wrap gap-x-5 gap-y-2 rounded-lg p-2 sm:flex-row sm:items-center',
                                isToday ? 'bg-background-accent-less' : 'hover:bg-background-more',
                            )}
                        >
                            <span aria-hidden="true" className="h6 w-44">
                                {getDayName(openingHours.dayOfWeek, dayOfWeek)} <span>{formatDate(date)}</span>
                            </span>

                            <span aria-hidden="true">
                                {isClosedWholeDay ? (
                                    <>{t('Closed')}</>
                                ) : (
                                    openingHoursRanges.map(({ openingTime, closingTime }, index) => {
                                        const openingFormatted = formatAccessibleTime(openingTime, lang);
                                        const closingFormatted = formatAccessibleTime(closingTime, lang);

                                        return (
                                            <Fragment key={index}>
                                                {index > 0 && ','} {openingFormatted} - {closingFormatted}
                                            </Fragment>
                                        );
                                    })
                                )}
                            </span>

                            {isToday && (
                                <OpeningStatus className="self-baseline sm:self-auto" status={openingHours.status} />
                            )}
                        </div>
                    );
                })}
            </div>
        </>
    );
};
