import { describe, expect, it } from 'vitest';
import { getSkipSnapshots, validateSnapshotMode } from '../../cypress/snapshotMode';

describe('Cypress snapshot mode', () => {
    it.each([undefined, '0'])('keeps snapshots enabled for SKIP_SNAPSHOTS=%s', (value) => {
        expect(getSkipSnapshots(value)).toBe(false);
    });

    it('enables functional-only runs explicitly', () => {
        expect(getSkipSnapshots('1')).toBe(true);
        expect(() => validateSnapshotMode(true, 'regression')).not.toThrow();
    });

    it.each(['', 'true', 'false', '2'])('rejects an ambiguous SKIP_SNAPSHOTS=%s setting', (value) => {
        expect(() => getSkipSnapshots(value)).toThrow('SKIP_SNAPSHOTS must be 0 or 1.');
    });

    it('refuses to regenerate references with snapshots disabled', () => {
        expect(() => validateSnapshotMode(true, 'base')).toThrow(
            'Cannot generate screenshot references with skipSnapshots enabled.',
        );
    });

    it('allows reference regeneration with snapshots enabled', () => {
        expect(() => validateSnapshotMode(false, 'base')).not.toThrow();
    });
});
