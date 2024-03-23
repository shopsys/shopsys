import { OpeningHours as OpeningHoursType } from 'graphql/types';
import { twMergeCustom } from 'helpers/twMerge';
import { useFormatDate } from 'hooks/formatting/useFormatDate';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

export const OpeningHours: FC<{ openingHours: OpeningHoursType }> = ({ openingHours, className }) => {
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

    return (
        <div className={twMergeCustom('flex w-full flex-col text-sm', className)}>
            {openingHours.openingHoursOfDays.map(({ date, dayOfWeek, openingHoursRanges }) => {
                const isToday = openingHours.dayOfWeek === dayOfWeek;
                const isClosedWholeDay = openingHoursRanges.length === 0;

                return (
                    <div
                        key={dayOfWeek}
                        className={twJoin(
                            'flex flex-row',
                            isToday && openingHours.isOpen && 'text-greenDark',
                            isToday && !openingHours.isOpen && 'text-orange',
                            isToday && isClosedWholeDay && 'text-red',
                        )}
                    >
                        <strong className="basis-32 text-left">
                            {getDayName(openingHours.dayOfWeek, dayOfWeek)} {formatDate(date, 'D.M.')}
                        </strong>
                        <span className="flex-1">
                            {isClosedWholeDay ? (
                                <>&nbsp;{t('Closed')}</>
                            ) : (
                                openingHoursRanges.map(({ openingTime, closingTime }, index) => (
                                    <>
                                        {index > 0 && ','} {openingTime}&nbsp;-&nbsp;{closingTime}
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
