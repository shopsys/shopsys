import { TypePickupPlaceOpeningHoursInput } from 'graphql/types';
import { StoreOrPacketeryPoint } from './types';

const TIME_REGEX = /^([01]\d|2[0-3]):[0-5]\d$/;
const ISO_DATE_REGEX = /^\d{4}-\d{2}-\d{2}$/;

const isPickupPlaceOpeningHoursInputValid = (pickupPlaceOpeningHours: TypePickupPlaceOpeningHoursInput): boolean => {
    const openingHoursRanges = [
        ...pickupPlaceOpeningHours.openingHoursOfDays,
        ...pickupPlaceOpeningHours.exceptionDays,
    ].flatMap((day) => day.openingHoursRanges);

    return (
        openingHoursRanges.every(
            ({ openingTime, closingTime }) => TIME_REGEX.test(openingTime) && TIME_REGEX.test(closingTime),
        ) &&
        pickupPlaceOpeningHours.exceptionDays.every(
            ({ from, to }) => ISO_DATE_REGEX.test(from) && (to === null || to === undefined || ISO_DATE_REGEX.test(to)),
        )
    );
};

export const mapPickupPlaceOpeningHoursToInput = (
    pickupPlace: StoreOrPacketeryPoint | null,
): TypePickupPlaceOpeningHoursInput | null => {
    const exceptionDays = pickupPlace?.openingHours.exceptionDays;

    if (!pickupPlace || exceptionDays === undefined) {
        return null;
    }

    const pickupPlaceOpeningHours: TypePickupPlaceOpeningHoursInput = {
        openingHoursOfDays: pickupPlace.openingHours.openingHoursOfDays.map(({ dayOfWeek, openingHoursRanges }) => ({
            dayOfWeek,
            openingHoursRanges: openingHoursRanges.map(({ openingTime, closingTime }) => ({
                openingTime,
                closingTime,
            })),
        })),
        exceptionDays: exceptionDays.map(({ from, to, times }) => ({
            from,
            to,
            openingHoursRanges: times.map(({ open, close }) => ({ openingTime: open, closingTime: close })),
        })),
    };

    return isPickupPlaceOpeningHoursInputValid(pickupPlaceOpeningHours) ? pickupPlaceOpeningHours : null;
};
