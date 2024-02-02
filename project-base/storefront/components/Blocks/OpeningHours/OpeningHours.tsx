import { OpeningHoursApi } from 'graphql/generated';
import { twMergeCustom } from 'helpers/twMerge';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

export const OpeningHours: FC<{ openingHours: OpeningHoursApi }> = ({ openingHours, className }) => {
    const { t } = useTranslation();

    const dayNames = [
        t('Monday'),
        t('Tuesday'),
        t('Wednesday'),
        t('Thursday'),
        t('Friday'),
        t('Saturday'),
        t('Sunday'),
    ];

    return (
        <div className={twMergeCustom('flex w-full flex-col items-center gap-2 text-left', className)}>
            {openingHours.openingHoursOfDays.map(({ dayOfWeek, openingHoursRanges }) => {
                const isToday = openingHours.dayOfWeek === dayOfWeek;
                const isClosedWholeDay = openingHoursRanges.length === 0;

                return (
                    <div
                        key={dayOfWeek}
                        className={twJoin(
                            'flex w-full flex-col items-center md:w-auto md:flex-row',
                            isToday ? 'font-bold' : 'font-normal',
                        )}
                    >
                        <span className="mr-1 md:basis-28">{dayNames[dayOfWeek - 1]}:</span>
                        <span className="flex-1">
                            {isClosedWholeDay ? (
                                <>&nbsp;{t('Closed')}</>
                            ) : (
                                openingHoursRanges.map(({ openingTime, closingTime }, index) => (
                                    <>
                                        {index > 0 && ','}&nbsp;{openingTime}&nbsp;-&nbsp;{closingTime}
                                    </>
                                ))
                            )}
                        </span>
                    </div>
                );
            })}
        </div>
    );
};
