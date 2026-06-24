import {
    getComplaintsFilterFromUrlQuery,
    hasActiveComplaintListFiltersFromUrlQuery,
} from 'utils/complaints/getComplaintsFilterFromUrlQuery';
import { describe, expect, test } from 'vitest';

describe('getComplaintsFilterFromUrlQuery tests', () => {
    test('status should be used as active filter', () => {
        expect(hasActiveComplaintListFiltersFromUrlQuery({ status: 'new' })).toBe(true);
    });

    test('status should be mapped to filter status codes', () => {
        expect(getComplaintsFilterFromUrlQuery({ status: 'new' }, 'Europe/Prague')?.statusCodes).toStrictEqual(['new']);
    });

    test('date should be mapped as UTC date time filter value', () => {
        expect(getComplaintsFilterFromUrlQuery({ createdAfter: '2026-06-11' }, 'Europe/Prague')?.createdAfter).toBe(
            '2026-06-10T22:00:00+00:00',
        );
    });
});
