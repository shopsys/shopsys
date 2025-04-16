import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { describe, expect, test, vi } from 'vitest';

vi.mock('store/useSessionStore', () => ({}));

describe('getEndCursor tests', () => {
    test('end cursor should be empty string if loadMore is 0', () => {
        expect(getEndCursor(1, 0, DEFAULT_BLOG_PAGE_SIZE)).toBe('');
    });

    test('end cursor for n loadMore should be equal to (n + 1) page', () => {
        expect(getEndCursor(2, 0, DEFAULT_BLOG_PAGE_SIZE)).toBe(getEndCursor(1, 1, DEFAULT_BLOG_PAGE_SIZE));
    });

});
