import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { TIDs } from 'cypress/tids';
import { TypeOpeningHours } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { Fragment } from 'react';
import { twJoin } from 'tailwind-merge';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { twMergeCustom } from 'utils/twMerge';

export const OpeningHours: FC<{ openingHours: StoreOrPacketeryPoint['openingHours'] | TypeOpeningHours }> = ({
    openingHours,
    className,
}) => {
    const { t } = useTranslation();
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
                        <div
                            key={index}
                            className={twMergeCustom('flex w-full flex-col text-sm text-textError', className)}
                        >
                            {exceptionDayText}
                        </div>
                    );
                })}

            <div className={twMergeCustom('flex w-full flex-col text-text', className)} tid={TIDs.opening_hours}>
                {openingHours.openingHoursOfDays.map(({ date, dayOfWeek, openingHoursRanges }) => {
                    const isToday = openingHours.dayOfWeek === dayOfWeek;
                    const isClosedWholeDay = openingHoursRanges.length === 0;

                    return (
                        <div
                            key={dayOfWeek}
                            className={twJoin(
                                'flex flex-row items-center gap-2 px-2.5 py-1.5 vl:gap-5',
                                isToday && 'bg-backgroundAccentLess',
                            )}
                        >
                            <h6 className="basis-32 uppercase md:shrink-0">
                                {getDayName(openingHours.dayOfWeek, dayOfWeek)} {formatDate(date)}
                            </h6>
                            <span className="text-sm">
                                {isClosedWholeDay ? (
                                    <>{t('Closed')}</>
                                ) : (
                                    openingHoursRanges.map(({ openingTime, closingTime }, index) => (
                                        <Fragment key={index}>
                                            {index > 0 && ','} {openingTime}&nbsp;&#8209;&nbsp;{closingTime}
                                        </Fragment>
                                    ))
                                )}
                            </span>
                            {isToday && (
                                <span tid={TIDs.opening_hours_status}>
                                    <OpeningStatus isDynamic className="block" status={openingHours.status} />
                                </span>
                            )}
                        </div>
                    );
                })}
            </div>
        </>
    );
};
