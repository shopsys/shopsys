import { mapPacketeryExtendedPoint } from 'utils/packetery';
import { mapPickupPlaceOpeningHoursToInput } from 'utils/packetery/mapPickupPlaceOpeningHoursToInput';
import { PacketeryExtendedPoint, StoreOrPacketeryPoint } from 'utils/packetery/types';
import { describe, expect, test } from 'vitest';

const createPacketeryPoint = (
    regularOpeningHours: Record<string, string>,
    exceptionDays: PacketeryExtendedPoint['exceptionDays'] = [],
) =>
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
    } as unknown as PacketeryExtendedPoint);

const sortByDayOfWeek = <T extends { dayOfWeek: number }>(days: T[]) =>
    [...days].sort((firstDay, secondDay) => firstDay.dayOfWeek - secondDay.dayOfWeek);

describe('mapPickupPlaceOpeningHoursToInput', () => {
    test('returns null without a pickup place', () => {
        expect(mapPickupPlaceOpeningHoursToInput(null)).toBeNull();
    });

    test('returns null for a store, as the API knows its opening hours', () => {
        const store = {
            identifier: 'store-uuid',
            openingHours: {
                openingHoursOfDays: [
                    { dayOfWeek: 1, openingHoursRanges: [{ openingTime: '08:00', closingTime: '17:00' }] },
                ],
            },
        } as unknown as StoreOrPacketeryPoint;

        expect(mapPickupPlaceOpeningHoursToInput(store)).toBeNull();
    });

    test('maps the regular opening hours of a Packeta point, omitting the closed days', () => {
        const pickupPlaceOpeningHours = mapPickupPlaceOpeningHoursToInput(
            createPacketeryPoint({ monday: '08:00–12:00, 13:00–17:00', friday: 'Nonstop' }),
        );

        expect(sortByDayOfWeek(pickupPlaceOpeningHours!.openingHoursOfDays)).toStrictEqual([
            {
                dayOfWeek: 1,
                openingHoursRanges: [
                    { openingTime: '08:00', closingTime: '12:00' },
                    { openingTime: '13:00', closingTime: '17:00' },
                ],
            },
            { dayOfWeek: 5, openingHoursRanges: [{ openingTime: '00:00', closingTime: '23:59' }] },
        ]);
        expect(pickupPlaceOpeningHours!.exceptionDays).toStrictEqual([]);
    });

    test('maps a day with empty opening hours of a Packeta point as closed', () => {
        const pickupPlaceOpeningHours = mapPickupPlaceOpeningHoursToInput(
            createPacketeryPoint({ monday: '08:00–17:00', sunday: '' }),
        );

        expect(sortByDayOfWeek(pickupPlaceOpeningHours!.openingHoursOfDays)).toStrictEqual([
            { dayOfWeek: 1, openingHoursRanges: [{ openingTime: '08:00', closingTime: '17:00' }] },
            { dayOfWeek: 7, openingHoursRanges: [] },
        ]);
    });

    test('maps the exception days of a Packeta point', () => {
        const pickupPlaceOpeningHours = mapPickupPlaceOpeningHoursToInput(
            createPacketeryPoint({ monday: '08:00–17:00' }, [
                { from: '2026-12-24', to: '2026-12-26', times: [] },
                { from: '2026-12-31', to: null, times: [{ open: '08:00', close: '12:00' }] },
            ]),
        );

        expect(pickupPlaceOpeningHours!.exceptionDays).toStrictEqual([
            { from: '2026-12-24', to: '2026-12-26', openingHoursRanges: [] },
            { from: '2026-12-31', to: null, openingHoursRanges: [{ openingTime: '08:00', closingTime: '12:00' }] },
        ]);
    });

    test.each([
        ['a regular time', createPacketeryPoint({ monday: '8–17' })],
        [
            'an exception day time',
            createPacketeryPoint({ monday: '08:00–17:00' }, [
                { from: '2026-12-31', to: null, times: [{ open: '08:00', close: 'noon' }] },
            ]),
        ],
        [
            'an exception day date',
            createPacketeryPoint({ monday: '08:00–17:00' }, [{ from: '31.12.2026', to: null, times: [] }]),
        ],
    ])('returns null when %s has an unexpected format', (_, packeteryPoint) => {
        expect(mapPickupPlaceOpeningHoursToInput(packeteryPoint)).toBeNull();
    });
});
