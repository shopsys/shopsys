import { execFileSync } from 'node:child_process';
import { mkdtempSync, readFileSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { describe, expect, test } from 'vitest';

const storefrontRoot = path.resolve(__dirname, '..', '..');
const fixtureDirectory = path.join(storefrontRoot, 'vitest', 'biome', 'fixtures');
const biomeBinary = path.join(
    storefrontRoot,
    'node_modules',
    '.bin',
    process.platform === 'win32' ? 'biome.cmd' : 'biome',
);

const createBiomeConfigForFixtures = () => {
    const biomeConfigPath = path.join(storefrontRoot, 'biome.json');
    const biomeConfig = JSON.parse(readFileSync(biomeConfigPath, 'utf8')) as {
        files?: { includes?: string[] };
        plugins?: string[];
    };

    biomeConfig.files = {
        ...biomeConfig.files,
        includes: biomeConfig.files?.includes?.filter((entry) => entry !== '!vitest/biome/fixtures'),
    };
    biomeConfig.plugins = biomeConfig.plugins?.map((pluginPath) => path.resolve(storefrontRoot, pluginPath));

    const tempDirectory = mkdtempSync(path.join(tmpdir(), 'shopsys-biome-'));
    const tempConfigPath = path.join(tempDirectory, 'biome.json');

    writeFileSync(tempConfigPath, JSON.stringify(biomeConfig));

    return tempConfigPath;
};

const runBiomeLint = (fixtureName: string) => {
    const configPath = createBiomeConfigForFixtures();

    try {
        const output = execFileSync(
            biomeBinary,
            ['lint', path.join(fixtureDirectory, fixtureName), '--config-path', configPath],
            {
                cwd: storefrontRoot,
                encoding: 'utf8',
            },
        );

        return {
            output,
            status: 0,
        };
    } catch (error) {
        const execError = error as Error & {
            status?: number;
            stdout?: string | Buffer;
            stderr?: string | Buffer;
        };

        return {
            output: `${execError.stdout?.toString() ?? ''}${execError.stderr?.toString() ?? ''}`,
            status: execError.status ?? 1,
        };
    }
};

describe('Biome valid interactive content plugin', () => {
    test('reports div inside button', () => {
        const result = runBiomeLint('invalid-button-div.tsx');

        expect(result.status).toBe(1);
        expect(result.output).toContain(
            'Button elements cannot contain div elements. Use span or other phrasing content instead.',
        );
    });

    test('reports anchor inside button', () => {
        const result = runBiomeLint('invalid-button-anchor.tsx');

        expect(result.status).toBe(1);
        expect(result.output).toContain(
            'Button elements cannot contain anchor elements. Interactive elements cannot be nested inside other interactive elements.',
        );
    });

    test('reports nested button inside anchor', () => {
        const result = runBiomeLint('invalid-anchor-button.tsx');

        expect(result.status).toBe(1);
        expect(result.output).toContain(
            'Anchor elements cannot contain button elements. Interactive elements cannot be nested inside other interactive elements.',
        );
    });

    test('reports nested button inside button', () => {
        const result = runBiomeLint('invalid-button-button.tsx');

        expect(result.status).toBe(1);
        expect(result.output).toContain(
            'Button elements cannot contain other button elements. Interactive elements cannot be nested.',
        );
    });

    test('reports placeholder href on ExtendedNextLink without extra props', () => {
        const result = runBiomeLint('invalid-extended-next-link-href.tsx');

        expect(result.status).toBe(1);
        expect(result.output).toContain(
            'ExtendedNextLink components must not use placeholder href values. Use a button instead for actions that do not navigate.',
        );
    });

    test('reports placeholder href on ExtendedNextLink regardless of prop order', () => {
        const result = runBiomeLint('invalid-extended-next-link-reordered-href.tsx');

        expect(result.status).toBe(1);
        expect(result.output).toContain(
            'ExtendedNextLink components must not use placeholder href values. Use a button instead for actions that do not navigate.',
        );
    });

    test('reports placeholder href on Link wrapper', () => {
        const result = runBiomeLint('invalid-link-href.tsx');

        expect(result.status).toBe(1);
        expect(result.output).toContain(
            'Link components must not use placeholder href values. Use a button instead for actions that do not navigate.',
        );
    });

    test('allows valid interactive content', () => {
        const result = runBiomeLint('valid-interactive-content.tsx');

        expect(result.status).toBe(0);
    });
});
