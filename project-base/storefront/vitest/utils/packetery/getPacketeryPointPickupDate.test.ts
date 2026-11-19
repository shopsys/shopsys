import { getIsoDate } from 'utils/formaters/getIsoDate';
import { mapPacketeryExtendedPoint } from 'utils/packetery';
import { getPacketeryPointPickupDate } from 'utils/packetery/getPacketeryPointPickupDate';
import { PacketeryExtendedPoint } from 'utils/packetery/types';
import { describe, expect, test } from 'vitest';

const TIMEZONE = 'Europe/Prague';

// Packeta omits the closed days from the regular opening hours
const WORKING_DAYS_OPENING_HOURS = {
    monday: '08:00–12:00, 13:00–17:00',
    tuesday: '08:00–12:00, 13:00–15:30',
    wednesday: '08:00–12:00, 13:00–17:00',
    thursday: '08:00–12:00, 13:00–17:15',
    friday: '08:00–12:00, 13:00–17:00',
};

const NONSTOP_OPENING_HOURS = {
    monday: '00:00–23:59',
    tuesday: '00:00–23:59',
    wednesday: '00:00–23:59',
    thursday: '00:00–23:59',
    friday: '00:00–23:59',
    saturday: '00:00–23:59',
    sunday: '00:00–23:59',
};

// the starts of the days in the display timezone, as returned by the API
const FRIDAY = '2026-11-19T23:00:00+00:00';
const SATURDAY = '2026-11-20T23:00:00+00:00';

// before all the tested dates, so none of them is today
const NOW = new Date('2026-10-01T10:00:00Z');

const createOpeningHours = (
    regularOpeningHours: Record<string, string>,
    exceptionDays: PacketeryExtendedPoint['exceptionDays'] = [],
) =>
    // only the fields read by the mapping are provided
    mapPacketeryExtendedPoint({
        id: 2317,
        place: 'Pickup point',
        city: 'City',
        street: 'Street 1',
        zip: '582 82',
        country: 'cz',
        directions: '',
        openingHours: { regular: regularOpeningHours },
        exceptionDays,
    } as unknown as PacketeryExtendedPoint).openingHours;

const getPickupIsoDate = (pickupDate: string) => getIsoDate(pickupDate, TIMEZONE);

describe('getPacketeryPointPickupDate', () => {
    test('keeps the expected delivery date when the point is open on that day', () => {
        const pickupDate = getPacketeryPointPickupDate(
            FRIDAY,
            createOpeningHours(WORKING_DAYS_OPENING_HOURS),
            TIMEZONE,
            NOW,
        );

        expect(pickupDate).toBe(FRIDAY);
    });

    test('postpones the pickup to Monday when the point is closed on the weekend', () => {
        const pickupDate = getPacketeryPointPickupDate(
            SATURDAY,
            createOpeningHours(WORKING_DAYS_OPENING_HOURS),
            TIMEZONE,
            NOW,
        );

        expect(getPickupIsoDate(pickupDate)).toBe('2026-11-23');
    });

    test('keeps the weekend date for a point open every day', () => {
        const pickupDate = getPacketeryPointPickupDate(
            SATURDAY,
            createOpeningHours(NONSTOP_OPENING_HOURS),
            TIMEZONE,
            NOW,
        );

        expect(pickupDate).toBe(SATURDAY);
    });

    test('postpones the pickup after an exception closing the point for several days', () => {
        const openingHours = createOpeningHours(NONSTOP_OPENING_HOURS, [
            { from: '2026-11-20', to: '2026-11-24', times: [] },
        ]);

        const pickupDate = getPacketeryPointPickupDate(FRIDAY, openingHours, TIMEZONE, NOW);

        expect(getPickupIsoDate(pickupDate)).toBe('2026-11-25');
    });

    test('closes the point only on the start date of an exception without the end date', () => {
        const openingHours = createOpeningHours(NONSTOP_OPENING_HOURS, [{ from: '2026-11-20', to: null, times: [] }]);

        const pickupDate = getPacketeryPointPickupDate(FRIDAY, openingHours, TIMEZONE, NOW);

        expect(getPickupIsoDate(pickupDate)).toBe('2026-11-21');
    });

    test('allows the pickup on a regularly closed day opened by an exception', () => {
        const openingHours = createOpeningHours(WORKING_DAYS_OPENING_HOURS, [
            { from: '2026-11-21', to: null, times: [{ open: '09:00', close: '12:00' }] },
        ]);

        const pickupDate = getPacketeryPointPickupDate(SATURDAY, openingHours, TIMEZONE, NOW);

        expect(pickupDate).toBe(SATURDAY);
    });

    test('keeps today while the point is still open', () => {
        // Friday 16:30 in the display timezone, the point closes at 17:00
        const now = new Date('2026-11-20T15:30:00Z');

        const pickupDate = getPacketeryPointPickupDate(
            FRIDAY,
            createOpeningHours(WORKING_DAYS_OPENING_HOURS),
            TIMEZONE,
            now,
        );

        expect(pickupDate).toBe(FRIDAY);
    });

    test('postpones the pickup from today when the point has already closed', () => {
        // Friday 17:30 in the display timezone, the point closed at 17:00 and opens again on Monday
        const now = new Date('2026-11-20T16:30:00Z');

        const pickupDate = getPacketeryPointPickupDate(
            FRIDAY,
            createOpeningHours(WORKING_DAYS_OPENING_HOURS),
            TIMEZONE,
            now,
        );

        expect(getPickupIsoDate(pickupDate)).toBe('2026-11-23');
    });

    test('moves by calendar days across the daylight saving time change', () => {
        // Saturday 2026-10-24, the clocks go back on Sunday
        const pickupDate = getPacketeryPointPickupDate(
            '2026-10-23T22:00:00+00:00',
            createOpeningHours(WORKING_DAYS_OPENING_HOURS),
            TIMEZONE,
            NOW,
        );

        expect(getPickupIsoDate(pickupDate)).toBe('2026-10-26');
    });

    test('keeps the expected delivery date when the opening hours give no open day', () => {
        const pickupDate = getPacketeryPointPickupDate(FRIDAY, createOpeningHours({}), TIMEZONE, NOW);

        expect(pickupDate).toBe(FRIDAY);
    });
});
