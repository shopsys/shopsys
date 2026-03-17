import { isFullPageRequest } from 'utils/isFullPageRequest';
import { describe, expect, test } from 'vitest';

describe('isFullPageRequest', () => {
    test('returns true when x-nextjs-data header is missing', () => {
        expect(isFullPageRequest({})).toBe(true);
    });

    test('returns true when x-nextjs-data is undefined', () => {
        expect(isFullPageRequest({ 'x-nextjs-data': undefined })).toBe(true);
    });

    test('returns true when x-nextjs-data has a value other than 1', () => {
        expect(isFullPageRequest({ 'x-nextjs-data': '0' })).toBe(true);
        expect(isFullPageRequest({ 'x-nextjs-data': 'true' })).toBe(true);
    });

    test('returns false when x-nextjs-data is 1 (client-side navigation)', () => {
        expect(isFullPageRequest({ 'x-nextjs-data': '1' })).toBe(false);
    });
});
