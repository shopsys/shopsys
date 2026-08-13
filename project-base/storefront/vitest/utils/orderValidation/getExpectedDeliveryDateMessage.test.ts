import { getExpectedDeliveryDateMessage } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { describe, expect, test, vi } from 'vitest';

// Mock the translate function
const mockT = vi.fn((key: string, options?: any) => {
    if (options?.date !== undefined) {
        return key.replace('{{ date }}', options.date);
    }
    return key;
}) as any;

const NOW = new Date('2026-07-16T12:00:00Z'); // Thursday

describe('getExpectedDeliveryDateMessage test', () => {
    test('should return the today message for a delivery date of today', () => {
        const result = getExpectedDeliveryDateMessage('2026-07-16T00:00:00+00:00', false, NOW, 'UTC', 'en', mockT);

        expect(result).toBe('Delivery today 7/16');
    });

    test('should fall back to the plain date message for a delivery date in the past', () => {
        const result = getExpectedDeliveryDateMessage('2026-07-14T00:00:00+00:00', false, NOW, 'UTC', 'en', mockT);

        expect(result).toBe('Delivery 7/14');
    });

    test('should fall back to the plain date message for a pickup date in the past', () => {
        const result = getExpectedDeliveryDateMessage('2026-07-14T00:00:00+00:00', true, NOW, 'UTC', 'en', mockT);

        expect(result).toBe('Personal pickup 7/14');
    });

    test('should return the tomorrow message for a delivery date of tomorrow', () => {
        const result = getExpectedDeliveryDateMessage('2026-07-17T00:00:00+00:00', false, NOW, 'UTC', 'en', mockT);

        expect(result).toBe('Delivery tomorrow 7/17');
    });

    test('should return the day of week message for a delivery date within this week', () => {
        const result = getExpectedDeliveryDateMessage('2026-07-18T00:00:00+00:00', false, NOW, 'UTC', 'en', mockT);

        expect(result).toBe('Delivery on Saturday 7/18');
    });

    test('should return the day of week message for a delivery date within the next week', () => {
        const result = getExpectedDeliveryDateMessage('2026-07-26T00:00:00+00:00', false, NOW, 'UTC', 'en', mockT);

        expect(result).toBe('Delivery on Sunday 7/26');
    });

    test('should omit the day of week from the week after the next one on', () => {
        const result = getExpectedDeliveryDateMessage('2026-07-27T00:00:00+00:00', false, NOW, 'UTC', 'en', mockT);

        expect(result).toBe('Delivery 7/27');
    });

    test('should keep the day of week for the whole next week when ordering on Monday', () => {
        const mondayNow = new Date('2026-07-20T12:00:00Z');

        const result = getExpectedDeliveryDateMessage(
            '2026-07-28T00:00:00+00:00',
            false,
            mondayNow,
            'UTC',
            'en',
            mockT,
        );

        expect(result).toBe('Delivery on Tuesday 7/28');
    });

    test('should omit the day of week exactly from the Monday of the week after the next one', () => {
        const mondayNow = new Date('2026-07-20T12:00:00Z');

        const result = getExpectedDeliveryDateMessage(
            '2026-08-03T00:00:00+00:00',
            false,
            mondayNow,
            'UTC',
            'en',
            mockT,
        );

        expect(result).toBe('Delivery 8/3');
    });

    test('should return the personal pickup today message for a pickup date of today', () => {
        const result = getExpectedDeliveryDateMessage('2026-07-16T00:00:00+00:00', true, NOW, 'UTC', 'en', mockT);

        expect(result).toBe('Personal pickup today 7/16');
    });

    test('should return the personal pickup tomorrow message for a pickup date of tomorrow', () => {
        const result = getExpectedDeliveryDateMessage('2026-07-17T00:00:00+00:00', true, NOW, 'UTC', 'en', mockT);

        expect(result).toBe('Personal pickup tomorrow 7/17');
    });

    test('should return the personal pickup day of week message for a pickup date within the next week', () => {
        const result = getExpectedDeliveryDateMessage('2026-07-26T00:00:00+00:00', true, NOW, 'UTC', 'en', mockT);

        expect(result).toBe('Personal pickup on Sunday 7/26');
    });

    test('should omit the day of week from the personal pickup message from the week after the next one on', () => {
        const result = getExpectedDeliveryDateMessage('2026-07-27T00:00:00+00:00', true, NOW, 'UTC', 'en', mockT);

        expect(result).toBe('Personal pickup 7/27');
    });

    test('should evaluate the dates in the given timezone', () => {
        // 23:30 UTC is already 01:30 of the next day in Europe/Prague, so the delivery date is today
        const lateNow = new Date('2026-07-16T23:30:00Z');

        const result = getExpectedDeliveryDateMessage(
            '2026-07-17T00:00:00+02:00',
            false,
            lateNow,
            'Europe/Prague',
            'cs',
            mockT,
        );

        expect(result).toBe('Delivery today 17. 7.');
    });
});
