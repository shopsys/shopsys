import { createIntlDateTimeFormatter } from 'utils/formaters/createIntlDateTimeFormatter';
import { StoreOrPacketeryPoint } from './types';

const DAY_IN_MILLISECONDS = 24 * 60 * 60 * 1000;

/**
 * A safety bound for the postponing — opening hours without any open day within it are not usable
 */
const MAX_POSTPONE_DAYS = 366;

const getIsoDayOfWeekOfIsoDate = (isoDate: string): number => {
    const dayOfWeek = new Date(`${isoDate}T00:00:00Z`).getUTCDay();

    return dayOfWeek === 0 ? 7 : dayOfWeek;
};

/**
 * Packeta omits the days the point is closed from its regular opening hours,
 * and an exception day without any times closes the point for the whole day
 */
const getClosingTimesOnIsoDate = (openingHours: StoreOrPacketeryPoint['openingHours'], isoDate: string): string[] => {
    const exceptionDay = openingHours.exceptionDays?.find(({ from, to }) => from <= isoDate && isoDate <= (to ?? from));

    if (exceptionDay !== undefined) {
        return exceptionDay.times.map(({ close }) => close);
    }

    const isoDayOfWeek = getIsoDayOfWeekOfIsoDate(isoDate);

    return openingHours.openingHoursOfDays
        .filter(({ dayOfWeek }) => dayOfWeek === isoDayOfWeek)
        .flatMap(({ openingHoursRanges }) => openingHoursRanges.map(({ closingTime }) => closingTime));
};

/**
 * The API calculates the expected delivery date of Packeta from the transport settings only, as it does not know
 * the opening hours of the selected pickup point, so the parcel can be picked up on the first day the point is open
 * (today only until it closes); the date is kept when the opening hours give no open day within the safety bound
 */
export const getPacketeryPointPickupDate = (
    expectedDeliveryDate: string,
    openingHours: StoreOrPacketeryPoint['openingHours'],
    timezone: string,
    now: Date,
): string => {
    // en-CA formats the date as YYYY-MM-DD
    const isoDateFormatter = createIntlDateTimeFormatter(
        { year: 'numeric', month: '2-digit', day: '2-digit' },
        timezone,
        'en-CA',
    );
    const timeFormatter = createIntlDateTimeFormatter(
        { hour: '2-digit', minute: '2-digit', hourCycle: 'h23' },
        timezone,
        'en-GB',
    );
    const todayIsoDate = isoDateFormatter.format(now);
    const currentTime = timeFormatter.format(now);
    const expectedDeliveryTime = new Date(expectedDeliveryDate).getTime();

    for (let postponedDays = 0; postponedDays < MAX_POSTPONE_DAYS; postponedDays++) {
        // the API returns the start of the day in the display timezone, and the midday of each following day
        // stays within that calendar day even across a daylight saving time change
        const pickupDate = new Date(expectedDeliveryTime + (postponedDays + 0.5) * DAY_IN_MILLISECONDS);
        const pickupIsoDate = isoDateFormatter.format(pickupDate);
        const closingTimes = getClosingTimesOnIsoDate(openingHours, pickupIsoDate);
        // the HH:mm times compare correctly as strings
        const isPointOpenForPickup =
            pickupIsoDate === todayIsoDate
                ? closingTimes.some((closingTime) => closingTime > currentTime)
                : closingTimes.length > 0;

        if (isPointOpenForPickup) {
            return postponedDays === 0 ? expectedDeliveryDate : pickupDate.toISOString();
        }
    }

    return expectedDeliveryDate;
};
