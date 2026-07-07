import {
    getOrdersFilterFromUrlQuery,
    hasActiveOrderListFiltersFromUrlQuery,
} from 'utils/orders/getOrdersFilterFromUrlQuery';
import { describe, expect, test } from 'vitest';

describe('getOrdersFilterFromUrlQuery tests', () => {
    test('status should be used as active filter', () => {
        expect(hasActiveOrderListFiltersFromUrlQuery({ status: 'new' })).toBe(true);
    });

    test('status should be mapped to filter status codes', () => {
        expect(getOrdersFilterFromUrlQuery({ status: 'new' }, 'Europe/Prague')?.statusCodes).toStrictEqual(['new']);
    });

    test('date should be mapped as UTC date time filter value', () => {
        expect(getOrdersFilterFromUrlQuery({ createdAfter: '2026-06-11' }, 'Europe/Prague')?.createdAfter).toBe(
            '2026-06-10T22:00:00+00:00',
        );
    });
});
