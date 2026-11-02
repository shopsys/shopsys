import { describe, expect, it } from 'vitest';
import { createSnapshotIndexer } from '../../cypress/support/snapshotIndexing';

describe('snapshot IDs', () => {
    it('preserves explicit IDs across gaps, repeated calls and isolated tests', () => {
        const getTestContext = () => {
            throw new Error('Explicit IDs must not depend on Cypress test state');
        };
        const index = createSnapshotIndexer(2, 3, getTestContext);

        expect(index(1)).toBe('2-3-1');
        expect(index(6)).toBe('2-3-6');
        expect(index(6)).toBe('2-3-6');
        expect(createSnapshotIndexer(2, 3, getTestContext)(6)).toBe('2-3-6');
        expect(index(0)).toBe('2-3-0');
    });

    it('keeps legacy sequential IDs and resets to the test start on retries', () => {
        let context = { titlePath: ['first test'], retry: 0 };
        const index = createSnapshotIndexer(2, 3, () => context);

        expect(index()).toBe('2-3-0');
        expect(index()).toBe('2-3-1');
        context = { titlePath: ['second test'], retry: 0 };
        expect(index()).toBe('2-3-2');
        context.retry = 1;
        expect(index()).toBe('2-3-2');
        expect(index()).toBe('2-3-3');
        context.retry = 2;
        expect(index()).toBe('2-3-2');
        context = { titlePath: ['third test'], retry: 0 };
        expect(index()).toBe('2-3-3');
    });

    it('keeps retry IDs stable for identically named tests in different suites', () => {
        const title = 'should not display product prices on the category page';
        let context = { titlePath: ['Limited User', title], retry: 0 };
        const index = createSnapshotIndexer(8, 0, () => context);

        expect(index()).toBe('8-0-0');
        context.retry = 1;
        expect(index()).toBe('8-0-0');
        context.retry = 2;
        expect(index()).toBe('8-0-0');

        context = { titlePath: ['Catalog User', title], retry: 0 };
        expect(index()).toBe('8-0-1');
        context.retry = 1;
        expect(index()).toBe('8-0-1');
        context.retry = 2;
        expect(index()).toBe('8-0-1');
    });
});
