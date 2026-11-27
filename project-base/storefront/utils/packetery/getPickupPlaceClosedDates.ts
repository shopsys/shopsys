import { createIntlDateTimeFormatter } from 'utils/formaters/createIntlDateTimeFormatter';
import { StoreOrPacketeryPoint } from './types';

/**
 * The regular opening hours hold for any date, exception days are known only as far ahead as Packeta publishes them
 */
const CLOSED_DATES_DAYS_AHEAD = 60;

/**
 * Returns the YYYY-MM-DD dates the pickup place is closed on, so the API can postpone the expected delivery date
 * to the first day the place is open; today counts as closed once the place has closed for the day, as with stores
 */
export const getPickupPlaceClosedDates = (
    pickupPlace: StoreOrPacketeryPoint,
    now: Date,
    timezone: string,
): string[] => {
    const closedDates: string[] = [];

    // a pickup place restored from an older order has no opening hours, so nothing is known to be closed
    if (pickupPlace.openingHours.openingHoursOfDays.length === 0) {
        return closedDates;
    }

    // en-CA formats the date as YYYY-MM-DD
    const today = createIntlDateTimeFormatter(
        { year: 'numeric', month: '2-digit', day: '2-digit' },
        timezone,
        'en-CA',
    ).format(now);
    const currentTime = createIntlDateTimeFormatter(
        { hour: '2-digit', minute: '2-digit', hourCycle: 'h23' },
        timezone,
        'en-GB',
    ).format(now);

    for (let daysAhead = 0; daysAhead < CLOSED_DATES_DAYS_AHEAD; daysAhead++) {
        const date = new Date(`${today}T00:00:00Z`);
        date.setUTCDate(date.getUTCDate() + daysAhead);
        const formattedDate = date.toISOString().split('T')[0];
        const closingTimes = getClosingTimesOnDate(pickupPlace, formattedDate, date.getUTCDay() || 7);

        // the widget reports the times as zero-padded HH:MM, so they compare as strings, the same way as stores on the API
        const isOpen =
            daysAhead === 0 ? closingTimes.some((closingTime) => closingTime > currentTime) : closingTimes.length > 0;

        if (!isOpen) {
            closedDates.push(formattedDate);
        }
    }

    return closedDates;
};

const getClosingTimesOnDate = (
    pickupPlace: StoreOrPacketeryPoint,
    formattedDate: string,
    dayOfWeek: number,
): string[] => {
    // the widget reports the exception days as date-times like 2026-12-24T00:00:00, only their dates matter
    const exceptionDay = pickupPlace.openingHours.exceptionDays?.find(
        ({ from, to }) => from.split('T')[0] <= formattedDate && formattedDate <= (to ?? from).split('T')[0],
    );

    if (exceptionDay) {
        return exceptionDay.times.map(({ close }) => close);
    }

    return (
        pickupPlace.openingHours.openingHoursOfDays
            .find((openingHoursOfDay) => openingHoursOfDay.dayOfWeek === dayOfWeek)
            ?.openingHoursRanges.map(({ closingTime }) => closingTime) ?? []
    );
};
