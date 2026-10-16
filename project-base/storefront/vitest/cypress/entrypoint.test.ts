import { spawnSync } from 'node:child_process';
import { mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

describe('Cypress container command selection', () => {
    let directory: string;

    beforeEach(() => {
        directory = mkdtempSync(join(tmpdir(), 'cypress-entrypoint-'));
        writeFileSync(
            join(directory, 'npm'),
            '#!/bin/sh\nprintf "%s|skip=%s\\n" "$*" "$SKIP_SNAPSHOTS" >> "$COMMAND_LOG"\nif [ "$1" = run ]; then exit "$NPM_EXIT"; fi\n',
            { mode: 0o755 },
        );
        writeFileSync(join(directory, 'calls'), '');
    });

    afterEach(() => {
        rmSync(directory, { recursive: true, force: true });
    });

    const run = (settings: Record<string, string | undefined> = {}) => {
        const result = spawnSync('sh', [resolve('cypress/docker/entrypoint.sh')], {
            cwd: directory,
            env: {
                PATH: `${directory}:/usr/bin:/bin`,
                COMMAND_LOG: join(directory, 'calls'),
                NODE_ENV: 'test',
                NPM_EXIT: '0',
                ...settings,
            },
            encoding: 'utf8',
            timeout: 5000,
        });
        return { ...result, calls: readFileSync(join(directory, 'calls'), 'utf8') };
    };

    it('defaults to comparison with screenshots enabled', () => {
        const result = run();
        expect(result.status).toBe(0);
        expect(result.calls).toContain('run regression|skip=0');
    });

    it.each([
        ['run', 'regression'],
        ['selected', 'selected-regression'],
        ['open', 'open-regression'],
        ['smoke', 'smoke'],
    ])('passes functional-only mode through the %s command', (command, script) => {
        const result = run({ COMMAND: command, SKIP_SNAPSHOTS: '1' });
        expect(result.status).toBe(0);
        expect(result.calls).toContain(`run ${script}|skip=1`);
    });

    it('selects the specific-spec script without changing snapshot mode', () => {
        const result = run({ SPEC: 'e2e/cart/cartLogin.cy.ts', SKIP_SNAPSHOTS: '1' });
        expect(result.status).toBe(0);
        expect(result.calls).toContain('run specific-regression e2e/cart/cartLogin.cy.ts|skip=1');
    });

    it.each([
        ['run', 'base'],
        ['selected', 'selected-base'],
        ['open', 'open-base'],
    ])('keeps explicit %s reference generation available', (command, script) => {
        const result = run({ TYPE: 'base', COMMAND: command });
        expect(result.status).toBe(0);
        expect(result.calls).toContain(`run ${script}|skip=0`);
    });

    it.each([
        { TYPE: 'base', SKIP_SNAPSHOTS: '1' },
        { TYPE: 'typo' },
        { COMMAND: 'typo' },
        { SKIP_SNAPSHOTS: 'true' },
        { SKIP_SNAPSHOTS: '' },
    ])('rejects invalid settings before invoking npm: %j', (settings) => {
        const result = run(settings);
        expect(result.status).toBe(1);
        expect(result.calls).toBe('');
    });

    it('returns a failure from npm instead of claiming success', () => {
        expect(run({ NPM_EXIT: '7' }).status).toBe(7);
    });
});
