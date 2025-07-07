import { getDeliveryMessage } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { describe, expect, test, vi } from 'vitest';

vi.mock('next/config', () => ({
    default: () => ({
        serverRuntimeConfig: { internalGraphqlEndpoint: 'https://test.ts/graphql/' },
        publicRuntimeConfig: {
            errorDebuggingLevel: 'no-debug',
            domains: [{ url: 'https://test.ts/' }, { url: 'https://test.ts/' }],
        },
    }),
}));

// Mock the translate function
const mockT = vi.fn((key: string, options?: any) => {
    if (options?.count !== undefined) {
        return `${key}:${options.count}`;
    }
    return key;
}) as any;

describe('getDeliveryMessage test', () => {
    describe('Personal pickup messages', () => {
        test('should return "Personal pickup today" for same day personal pickup', () => {
            const result = getDeliveryMessage(0, true, mockT);

            expect(result).toBe('Personal pickup today');
            expect(mockT).toHaveBeenCalledWith('Personal pickup today');
        });

        test('should return personal pickup message with days for pickup within week', () => {
            const result = getDeliveryMessage(3, true, mockT);

            expect(result).toBe('Personal pickup in {{ count }} days:3');
            expect(mockT).toHaveBeenCalledWith('Personal pickup in {{ count }} days', { count: 3 });
        });

        test('should return personal pickup message with weeks for pickup over week', () => {
            const result = getDeliveryMessage(10, true, mockT);

            expect(result).toBe('Personal pickup in {{count}} weeks:2');
            expect(mockT).toHaveBeenCalledWith('Personal pickup in {{count}} weeks', { count: 2 });
        });

        test('should return personal pickup message with weeks for exactly 7 days', () => {
            const result = getDeliveryMessage(7, true, mockT);

            expect(result).toBe('Personal pickup in {{count}} weeks:1');
            expect(mockT).toHaveBeenCalledWith('Personal pickup in {{count}} weeks', { count: 1 });
        });

        test('should return personal pickup message with weeks for 14 days', () => {
            const result = getDeliveryMessage(14, true, mockT);

            expect(result).toBe('Personal pickup in {{count}} weeks:2');
            expect(mockT).toHaveBeenCalledWith('Personal pickup in {{count}} weeks', { count: 2 });
        });

        test('should round up weeks correctly for personal pickup', () => {
            const result = getDeliveryMessage(8, true, mockT);

            expect(result).toBe('Personal pickup in {{count}} weeks:2');
            expect(mockT).toHaveBeenCalledWith('Personal pickup in {{count}} weeks', { count: 2 });
        });
    });

    describe('Regular delivery messages', () => {
        test('should return "Delivery today" for same day delivery', () => {
            const result = getDeliveryMessage(0, false, mockT);

            expect(result).toBe('Delivery today');
            expect(mockT).toHaveBeenCalledWith('Delivery today');
        });

        test('should return delivery message with days for delivery within week', () => {
            const result = getDeliveryMessage(5, false, mockT);

            expect(result).toBe('Delivery in {{count}} days:5');
            expect(mockT).toHaveBeenCalledWith('Delivery in {{count}} days', { count: 5 });
        });

        test('should return delivery message with weeks for delivery over week', () => {
            const result = getDeliveryMessage(10, false, mockT);

            expect(result).toBe('Delivery in {{count}} weeks:2');
            expect(mockT).toHaveBeenCalledWith('Delivery in {{count}} weeks', { count: 2 });
        });

        test('should return delivery message with weeks for exactly 7 days', () => {
            const result = getDeliveryMessage(7, false, mockT);

            expect(result).toBe('Delivery in {{count}} weeks:1');
            expect(mockT).toHaveBeenCalledWith('Delivery in {{count}} weeks', { count: 1 });
        });

        test('should round up weeks correctly for regular delivery', () => {
            const result = getDeliveryMessage(9, false, mockT);

            expect(result).toBe('Delivery in {{count}} weeks:2');
            expect(mockT).toHaveBeenCalledWith('Delivery in {{count}} weeks', { count: 2 });
        });
    });

    describe('Edge cases', () => {
        test('should handle negative days as passed value', () => {
            const result = getDeliveryMessage(-1, false, mockT);

            expect(result).toBe('Delivery in {{count}} days:-1');
            expect(mockT).toHaveBeenCalledWith('Delivery in {{count}} days', { count: -1 });
        });

        test('should handle very large number of days', () => {
            const result = getDeliveryMessage(100, false, mockT);

            expect(result).toBe('Delivery in {{count}} weeks:15');
            expect(mockT).toHaveBeenCalledWith('Delivery in {{count}} weeks', { count: 15 });
        });
    });
});
