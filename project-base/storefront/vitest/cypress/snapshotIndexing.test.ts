import { execFileSync } from 'node:child_process';
import { mkdirSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
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
        let context = { title: 'first test', retry: 0 };
        const index = createSnapshotIndexer(2, 3, () => context);

        expect(index()).toBe('2-3-0');
        expect(index()).toBe('2-3-1');
        context = { title: 'second test', retry: 0 };
        expect(index()).toBe('2-3-2');
        context.retry = 1;
        expect(index()).toBe('2-3-2');
        expect(index()).toBe('2-3-3');
        context.retry = 2;
        expect(index()).toBe('2-3-2');
        context = { title: 'third test', retry: 0 };
        expect(index()).toBe('2-3-3');
    });

    it('generates the lookup table with stable IDs and multiline test titles', () => {
        const directory = mkdtempSync(join(tmpdir(), 'shopsys-snapshot-table-'));
        try {
            mkdirSync(join(directory, 'support'));
            mkdirSync(join(directory, 'e2e'));
            writeFileSync(join(directory, 'support/index.ts'), 'export enum SNAPSHOT_GROUP { CART = 2 }');
            writeFileSync(
                join(directory, 'e2e/stable.cy.ts'),
                `const SUBGROUP_INDEX = 3;
                const index = getSnapshotIndexingFunction(SNAPSHOT_GROUP.CART, SUBGROUP_INDEX);
                it('first', () => {
                    takeSnapshotAndCompare(getSnapshotFullIndexAsString(1), 'popup');
                });
                it(
                    'second',
                    { retries: 0 },
                    () => { takeSnapshotAndCompare(getSnapshotFullIndexAsString(6), 'card'); },
                );`,
            );
            writeFileSync(
                join(directory, 'e2e/legacy.cy.ts'),
                `const SUBGROUP_INDEX = 4;
                const index = getSnapshotIndexingFunction(SNAPSHOT_GROUP.CART, SUBGROUP_INDEX);
                it('legacy', () => {
                    takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'before');
                    takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after');
                });`,
            );

            const generate = () =>
                execFileSync(process.execPath, [resolve('cypress/generateSnapshotsInfoTable.js')], { cwd: directory });
            generate();
            const table = readFileSync(join(directory, 'snapshots-info-table.md'), 'utf8');

            expect(table).toContain('| 2-3-1 | first | popup | stable.cy.ts |');
            expect(table).toContain('| 2-3-6 | second | card | stable.cy.ts |');
            expect(table).toContain('| 2-4-0 | legacy | before | legacy.cy.ts |');
            expect(table).toContain('| 2-4-1 | legacy | after | legacy.cy.ts |');
            expect(table).not.toContain('2-3-0');
            generate();
            expect(readFileSync(join(directory, 'snapshots-info-table.md'), 'utf8')).toBe(table);
        } finally {
            rmSync(directory, { recursive: true, force: true });
        }
    });
});
