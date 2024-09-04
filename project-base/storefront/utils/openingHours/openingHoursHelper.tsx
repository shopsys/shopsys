import { TypeOpeningHoursFragment } from 'graphql/requests/stores/fragments/OpeningHoursFragment.generated';
import { Fragment } from 'react';

export const getTodayOpeningHours = (openingHours: TypeOpeningHoursFragment) => {
    const todayOpeningDayRanges = openingHours.openingHoursOfDays[0].openingHoursRanges;

    if (todayOpeningDayRanges.length === 0) {
        return null;
    }

    return (
        <>
            {todayOpeningDayRanges.map(({ openingTime, closingTime }, index) => (
                <Fragment key={index}>
                    {index > 0 && ','} {openingTime}&nbsp;&#8209;&nbsp;{closingTime}
                </Fragment>
            ))}
        </>
    );
};
