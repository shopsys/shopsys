import { createIntlDateTimeFormatter } from 'utils/formaters/createIntlDateTimeFormatter';
import { mapPacketeryExtendedPoint } from 'utils/packetery';
import { PacketeryExtendedPoint } from 'utils/packetery/types';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const TIMEZONE = 'Europe/Prague';

describe('mapPacketeryExtendedPoint', () => {
    beforeEach(() => {
        // Friday in winter time
        vi.useFakeTimers();
        vi.setSystemTime(new Date('2026-11-27T10:00:00Z'));
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('dates the opening hours of days to the following week starting today', () => {
        const packeteryPoint = mapPacketeryExtendedPoint({
            id: 13789,
            zip: '102 00',
            country: 'cz',
            openingHours: {
                regular: {
                    monday: '08:00–18:00',
                    tuesday: '08:00–18:00',
                    wednesday: '08:00–18:00',
                    thursday: '08:00–18:00',
                    friday: '08:00–18:00',
                    saturday: '08:00–12:00',
                    sunday: '',
                },
            },
            exceptionDays: [],
        } as unknown as PacketeryExtendedPoint);

        const dateFormatter = createIntlDateTimeFormatter(
            { year: 'numeric', month: '2-digit', day: '2-digit' },
            TIMEZONE,
            'en-CA',
        );

        expect(
            packeteryPoint.openingHours.openingHoursOfDays.map(({ date }) => dateFormatter.format(new Date(date))),
        ).toEqual(['2026-11-27', '2026-11-28', '2026-11-29', '2026-11-30', '2026-12-01', '2026-12-02', '2026-12-03']);
    });
});
