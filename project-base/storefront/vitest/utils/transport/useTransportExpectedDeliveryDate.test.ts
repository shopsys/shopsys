import { renderHook } from '@testing-library/react';
import { TypeTransportTypeEnum } from 'graphql/types';
import { getIsoDate } from 'utils/formaters/getIsoDate';
import { mapPacketeryExtendedPoint } from 'utils/packetery';
import { PacketeryExtendedPoint } from 'utils/packetery/types';
import { useTransportExpectedDeliveryDate } from 'utils/transport/useTransportExpectedDeliveryDate';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const { TIMEZONE } = vi.hoisted(() => ({ TIMEZONE: 'Europe/Prague' }));

vi.mock('utils/formatting/useDisplayTimezone', () => ({
    useDisplayTimezone: () => TIMEZONE,
}));

beforeEach(() => {
    vi.useFakeTimers({ toFake: ['Date'] });
    // Thursday before the tested dates
    vi.setSystemTime(new Date('2026-11-19T12:00:00Z'));
});

afterEach(() => {
    vi.useRealTimers();
});

// the start of Saturday 2026-11-21 in the display timezone, as returned by the API
const SATURDAY = '2026-11-20T23:00:00+00:00';

// only the fields read by the mapping are provided, the point is closed on the weekend
const pickupPlaceClosedOnWeekend = mapPacketeryExtendedPoint({
    id: 2317,
    place: 'Pickup point',
    city: 'City',
    street: 'Street 1',
    zip: '582 82',
    country: 'cz',
    directions: '',
    openingHours: {
        regular: {
            monday: '08:00–17:00',
            tuesday: '08:00–17:00',
            wednesday: '08:00–17:00',
            thursday: '08:00–17:00',
            friday: '08:00–17:00',
        },
    },
    exceptionDays: [],
} as unknown as PacketeryExtendedPoint);

const renderTransportExpectedDeliveryDate = (
    transportTypeCode: TypeTransportTypeEnum,
    expectedDeliveryDate: string | null,
    selectedPickupPlace: typeof pickupPlaceClosedOnWeekend | null,
) =>
    renderHook(() => useTransportExpectedDeliveryDate({ expectedDeliveryDate, transportTypeCode }, selectedPickupPlace))
        .result.current;

describe('useTransportExpectedDeliveryDate', () => {
    test('respects the opening hours of the selected Packeta pickup point', () => {
        const expectedDeliveryDate = renderTransportExpectedDeliveryDate(
            TypeTransportTypeEnum.Packetery,
            SATURDAY,
            pickupPlaceClosedOnWeekend,
        );

        expect(getIsoDate(expectedDeliveryDate ?? '', TIMEZONE)).toBe('2026-11-23');
    });

    test('keeps the date of Packeta until a pickup point is selected', () => {
        const expectedDeliveryDate = renderTransportExpectedDeliveryDate(
            TypeTransportTypeEnum.Packetery,
            SATURDAY,
            null,
        );

        expect(expectedDeliveryDate).toBe(SATURDAY);
    });

    test('keeps the date of a personal pickup store, which already respects its opening hours', () => {
        const expectedDeliveryDate = renderTransportExpectedDeliveryDate(
            TypeTransportTypeEnum.PersonalPickup,
            SATURDAY,
            pickupPlaceClosedOnWeekend,
        );

        expect(expectedDeliveryDate).toBe(SATURDAY);
    });

    test('keeps an unknown date of Packeta unknown', () => {
        const expectedDeliveryDate = renderTransportExpectedDeliveryDate(
            TypeTransportTypeEnum.Packetery,
            null,
            pickupPlaceClosedOnWeekend,
        );

        expect(expectedDeliveryDate).toBeNull();
    });
});
