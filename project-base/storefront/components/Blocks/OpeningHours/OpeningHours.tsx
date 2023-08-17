import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'helpers/twMerge';
import { OpeningHoursApi } from 'graphql/requests/types';

export const OpeningHours: FC<{ openingHours: OpeningHoursApi }> = ({ openingHours, className }) => {
    const t = useTypedTranslationFunction();

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
        <div className={twMergeCustom('flex w-full flex-col items-center text-left', className)}>
            {openingHours.openingHoursOfDays.map(
                ({ firstOpeningTime, firstClosingTime, secondOpeningTime, secondClosingTime, dayOfWeek }) => {
                    const isToday = openingHours.dayOfWeek === dayOfWeek;
                    const isClosedWholeDay =
                        (!firstOpeningTime || !firstClosingTime) && (!secondOpeningTime || !secondClosingTime);
                    const isFirstTime = firstOpeningTime && firstClosingTime;
                    const isSecondTime = secondOpeningTime && secondClosingTime;

                    return (
                        <div
                            key={dayOfWeek}
                            className={twJoin(
                                'flex flex-col items-center py-1 md:flex-row',
                                isToday ? 'font-bold' : 'font-normal',
                            )}
                        >
                            <span className="mr-1 md:basis-28">{dayNames[dayOfWeek - 1]}:</span>
                            <span className="flex-1">
                                {isFirstTime && (
                                    <>
                                        {firstOpeningTime}&nbsp;-&nbsp;{firstClosingTime}
                                    </>
                                )}
                                {isFirstTime && isSecondTime && ','}
                                {isSecondTime && (
                                    <>
                                        &nbsp;{secondOpeningTime}&nbsp;-&nbsp;{secondClosingTime}
                                    </>
                                )}
                                {isClosedWholeDay && <>&nbsp;{t('Closed')}</>}
                            </span>
                        </div>
                    );
                },
            )}
        </div>
    );
};
