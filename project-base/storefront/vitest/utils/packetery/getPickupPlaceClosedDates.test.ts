import { mapPacketeryExtendedPoint } from 'utils/packetery';
import { getPickupPlaceClosedDates } from 'utils/packetery/getPickupPlaceClosedDates';
import { PacketeryExtendedPoint, StoreOrPacketeryPoint } from 'utils/packetery/types';
import { describe, expect, it } from 'vitest';

const TIMEZONE = 'Europe/Prague';

const workingDayRanges = [{ openingTime: '08:00', closingTime: '18:00' }];

// open from Monday to Saturday, closed on Sundays, on 1 December and over Christmas
const pickupPlace = {
    openingHours: {
        openingHoursOfDays: [
            ...[1, 2, 3, 4, 5].map((dayOfWeek) => ({ dayOfWeek, openingHoursRanges: workingDayRanges })),
            { dayOfWeek: 6, openingHoursRanges: [{ openingTime: '08:00', closingTime: '12:00' }] },
            { dayOfWeek: 7, openingHoursRanges: [] },
        ],
        exceptionDays: [
            { from: '2026-12-01T00:00:00', to: null, times: [] },
            { from: '2026-12-24T00:00:00', to: '2026-12-26T00:00:00', times: [] },
        ],
    },
} as unknown as StoreOrPacketeryPoint;

describe('getPickupPlaceClosedDates', () => {
    it('returns the regularly closed days and the exception days within the following 60 days', () => {
        // Friday 11:00 in Prague, the place is still open today
        const closedDates = getPickupPlaceClosedDates(pickupPlace, new Date('2026-11-27T10:00:00Z'), TIMEZONE);

        expect(closedDates).toEqual([
            '2026-11-29',
            '2026-12-01',
            '2026-12-06',
            '2026-12-13',
            '2026-12-20',
            '2026-12-24',
            '2026-12-25',
            '2026-12-26',
            '2026-12-27',
            '2027-01-03',
            '2027-01-10',
            '2027-01-17',
            '2027-01-24',
        ]);
    });

    it('returns today once the place has closed for the day', () => {
        // Friday 18:30 in Prague, the place closed at 18:00
        const closedDates = getPickupPlaceClosedDates(pickupPlace, new Date('2026-11-27T17:30:00Z'), TIMEZONE);

        expect(closedDates[0]).toBe('2026-11-27');
    });

    it('resolves today in the given timezone', () => {
        // still Saturday in UTC, but already Sunday 00:30 in Prague
        const closedDates = getPickupPlaceClosedDates(pickupPlace, new Date('2026-11-28T23:30:00Z'), TIMEZONE);

        expect(closedDates[0]).toBe('2026-11-29');
    });

    it('returns no dates when the opening hours of the place are unknown', () => {
        const pickupPlaceWithoutOpeningHours = {
            openingHours: { openingHoursOfDays: [] },
        } as unknown as StoreOrPacketeryPoint;

        expect(
            getPickupPlaceClosedDates(pickupPlaceWithoutOpeningHours, new Date('2026-11-27T10:00:00Z'), TIMEZONE),
        ).toEqual([]);
    });

    it('considers the days missing in the opening hours reported by the widget as closed', () => {
        const packeteryPoint = mapPacketeryExtendedPoint({
            id: 13789,
            zip: '102 00',
            country: 'cz',
            openingHours: {
                // the widget leaves out the days the point is closed on, unlike the example in the Packeta docs
                regular: {
                    monday: '08:00–18:00',
                    tuesday: '08:00–18:00',
                    wednesday: '08:00–18:00',
                    thursday: '08:00–18:00',
                    friday: '08:00–18:00',
                },
            },
            exceptionDays: [],
        } as unknown as PacketeryExtendedPoint);

        const closedDates = getPickupPlaceClosedDates(packeteryPoint, new Date('2026-11-27T10:00:00Z'), TIMEZONE);

        expect(closedDates.slice(0, 2)).toEqual(['2026-11-28', '2026-11-29']);
    });
});
