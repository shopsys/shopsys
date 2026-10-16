const assert = require('node:assert/strict');
const { spawnSync } = require('node:child_process');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { test } = require('node:test');
const yaml = require('js-yaml');

// CI runs this in Check standards; the same command can be used in the monorepo's existing PHP container.
const workflow = yaml.load(fs.readFileSync(path.join(__dirname, '../workflows/docker-build.yaml'), 'utf8'));
const prepare = workflow.jobs['prepare-regenerated-snapshots'];
const publish = workflow.jobs['push-regenerated-snapshots'];
const referencePath = 'project-base/storefront/cypress/snapshots/e2e/cart/example.png';

function fixture(t) {
    const directory = fs.mkdtempSync(path.join(os.tmpdir(), 'snapshot-publication-test-'));
    t.after(() => fs.rmSync(directory, { recursive: true, force: true }));
    const write = (relativePath, contents) => {
        const filename = path.join(directory, relativePath);
        fs.mkdirSync(path.dirname(filename), { recursive: true });
        fs.writeFileSync(filename, contents);
        return filename;
    };
    const git = write(
        'bin/git',
        '#!/bin/sh\nprintf "%s\\n" "$*" >> git-calls\nif [ "$1" = diff ]; then exit "${TEST_DIFF_STATUS:-0}"; fi\n',
    );
    fs.chmodSync(git, 0o755);
    return { directory, write };
}

function runStep(job, stepName, fixture, extraEnv = {}) {
    const step = job.steps.find((item) => item.name === stepName);
    assert.ok(step, `Missing workflow step: ${stepName}`);
    return spawnSync('bash', ['-e', '-o', 'pipefail', '-c', step.run], {
        cwd: fixture.directory,
        env: { ...process.env, PATH: `${fixture.directory}/bin:${process.env.PATH}`, ...extraEnv },
        encoding: 'utf8',
        timeout: 5000,
    });
}

for (const [requested, subject, heading] of [
    ['true', 'ordinary change', 'generate screenshot references'],
    ['false', 'regenerated Cypress snapshots', 'verify regenerated screenshot references'],
    ['false', 'ordinary change', 'verify committed screenshot references'],
]) {
    test(`screenshot mode summary describes ${heading} without claiming success`, (t) => {
        const temporary = fixture(t);
        temporary.write('bin/git', '#!/bin/sh\nprintf "%s\\n" "$TEST_COMMIT_SUBJECT"\n');
        const summaryPath = temporary.write('summary.md', '');
        const result = runStep(workflow.jobs.variables, 'Describe Cypress screenshot mode', temporary, {
            REGENERATE_SCREENSHOTS: requested,
            REFERENCE_COMMIT: 'a'.repeat(40),
            GITHUB_SERVER_URL: 'https://github.com',
            GITHUB_REPOSITORY: 'shopsys/shopsys',
            GITHUB_STEP_SUMMARY: summaryPath,
            TEST_COMMIT_SUBJECT: subject,
        });
        assert.equal(result.status, 0, result.stderr);
        const summary = fs.readFileSync(summaryPath, 'utf8');
        assert.ok(summary.startsWith(`## Cypress: ${heading}\n`));
        if (requested === 'true') {
            assert.match(summary, /separate verification run/);
            assert.match(summary, /If references are unchanged/);
            assert.doesNotMatch(summary, /intentionally skipped/);
        } else {
            assert.ok(summary.includes(`https://github.com/shopsys/shopsys/commit/${'a'.repeat(40)}`));
            assert.match(summary, /intentionally skipped/);
            assert.match(summary, /not a successful test result/);
        }
    });
}

test('screenshot mode summary uses the PR head and the label-only job has a clear name', () => {
    const variables = workflow.jobs.variables;
    const summary = variables.steps.find((step) => step.name === 'Describe Cypress screenshot mode');
    assert.equal(summary.env.REFERENCE_COMMIT, '${{ github.event.pull_request.head.sha || github.sha }}');
    assert.equal(
        summary.env.REGENERATE_SCREENSHOTS,
        "${{ contains(github.event.pull_request.labels.*.name, 'regenerate screenshots') }}",
    );
    assert.match(summary.run, /git show -s --format=%s "\$REFERENCE_COMMIT"/);
    assert.equal(variables.steps.find((step) => step.uses?.startsWith('actions/checkout@')).with['fetch-depth'], 2);
    assert.equal(
        workflow.jobs['tests-storefront-acceptance-regenerate'].name,
        'Generate screenshot references (label only)',
    );
});

test('publication safeguards run as a blocking part of standards after dependency installation', () => {
    const standards = workflow.jobs.standards;
    const index = standards.steps.findIndex((step) => step.name === 'Check snapshot publication safeguards');
    assert.ok(index > standards.steps.findIndex((step) => step.run?.includes('npm-install-dependencies')));
    const step = standards.steps[index];
    assert.ok(step, 'Standards must run the publication safeguards');
    assert.match(
        step.run,
        /docker compose exec -T php-fpm node --test \.github\/tests\/cypress-snapshot-publication\.test\.cjs/,
    );
    assert.equal(step.if, undefined);
    assert.notEqual(step['continue-on-error'], true);
    assert.notEqual(standards['continue-on-error'], true);
    assert.ok(workflow.jobs['build-successful'].needs.includes('standards'));
});

test('PR dependency execution is isolated from the privileged publisher', () => {
    assert.ok(prepare, 'Comparison must run in a separate job');
    assert.deepEqual(prepare.permissions, { contents: 'read' });
    assert.doesNotMatch(JSON.stringify(prepare), /secrets\.|PUSH_TOKEN|pull-requests/);
    for (const job of [prepare, publish]) {
        assert.match(job.if, /github\.event\.pull_request\.head\.repo\.full_name == github\.repository/);
    }
    assert.deepEqual(publish.needs, ['prepare-regenerated-snapshots']);
    assert.match(publish.if, /needs\.prepare-regenerated-snapshots\.result == 'success'/);
    for (const step of publish.steps) {
        assert.doesNotMatch(step.run || '', /\b(?:npm|npx|node|pnpm)\b/);
        assert.doesNotMatch(step.run || '', /(?:bash|sh)\s+\.?\/?\.github\//);
    }
    const download = publish.steps.find((step) => step.uses?.startsWith('actions/download-artifact@'));
    assert.equal(download.with['artifact-ids'], '${{ needs.prepare-regenerated-snapshots.outputs.artifact_id }}');
    const upload = prepare.steps.find((step) => step.uses?.startsWith('actions/upload-artifact@'));
    assert.equal(upload.with.overwrite, true, 'Rerunning preparation must not collide with its previous artifact');
    assert.ok(
        !upload.with.name.startsWith('cypress-snapshots-'),
        'Do not consume the prepared artifact as raw input on rerun',
    );
});

test('Cypress execution and snapshot publication checkouts do not retain Git credentials', () => {
    for (const jobName of [
        'tests-storefront-acceptance',
        'tests-storefront-acceptance-regenerate',
        'prepare-regenerated-snapshots',
        'push-regenerated-snapshots',
    ]) {
        const job = workflow.jobs[jobName];
        assert.ok(job, `Missing workflow job: ${jobName}`);
        const checkouts = job.steps.filter((step) => step.uses?.startsWith('actions/checkout@'));
        assert.ok(checkouts.length > 0, `Missing checkout in ${jobName}`);
        for (const checkout of checkouts) {
            assert.equal(checkout.with?.['persist-credentials'], false, `${jobName} must not retain Git credentials`);
        }
    }
});

test('comparison has a bounded runtime and avoids the Node V8 shutdown deadlock', () => {
    assert.ok(prepare);
    const step = prepare.steps.find((item) => item.name === 'Filter regenerated PNG references');
    assert.match(step.run, /timeout --kill-after=5s 60s node --no-maglev --no-concurrent-sparkplug/);
    assert.match(step.run, /"\$TARGET" "\$SNAPSHOT" '' 0 false/);
    assert.ok(prepare['timeout-minutes'] <= 15);
    assert.ok(publish['timeout-minutes'] <= 10);
});

for (const [status, expected, passes] of [
    [0, 'original', true],
    [65, 'generated', true],
    [66, 'generated', true],
    [1, 'generated', false],
    [124, 'generated', false],
    [137, 'generated', false],
]) {
    test(`comparison exit ${status} ${passes ? 'preserves the intended bytes' : 'fails instead of publishing'}`, (t) => {
        assert.ok(prepare);
        const temporary = fixture(t);
        temporary.write(referencePath, 'original');
        temporary.write('snapshots/e2e/cart/example.png', 'generated');
        // Simulate only the comparator's documented exit contract; image semantics are verified with the real CLI separately.
        const timeout = temporary.write('bin/timeout', `#!/bin/sh\nexit ${status}\n`);
        fs.chmodSync(timeout, 0o755);
        const result = runStep(prepare, 'Filter regenerated PNG references', temporary);
        assert.equal(result.status === 0, passes, result.stderr);
        assert.equal(
            fs.readFileSync(path.join(temporary.directory, 'snapshots/e2e/cart/example.png'), 'utf8'),
            expected,
        );
    });
}

test('new screenshots do not need an old reference', (t) => {
    assert.ok(prepare);
    const temporary = fixture(t);
    temporary.write('snapshots/e2e/cart/new.png', 'new');
    const result = runStep(prepare, 'Filter regenerated PNG references', temporary);
    assert.equal(result.status, 0, result.stderr);
    assert.equal(fs.readFileSync(path.join(temporary.directory, 'snapshots/e2e/cart/new.png'), 'utf8'), 'new');
});

test('publisher copies PNG data and commits only when Git reports changes', (t) => {
    const temporary = fixture(t);
    temporary.write('snapshots/e2e/cart/example.png', 'generated');
    temporary.write(referencePath, 'original');
    const result = runStep(publish, 'Prepare PNG-only snapshot commit', temporary, { TEST_DIFF_STATUS: '1' });
    assert.equal(result.status, 0, result.stderr);
    assert.equal(fs.readFileSync(path.join(temporary.directory, referencePath), 'utf8'), 'generated');
    assert.match(
        fs.readFileSync(path.join(temporary.directory, 'git-calls'), 'utf8'),
        /commit -m regenerated Cypress snapshots/,
    );
});

test('unchanged publication succeeds without an empty commit, including repeated runs', (t) => {
    const temporary = fixture(t);
    temporary.write('snapshots/e2e/cart/example.png', 'original');
    temporary.write(referencePath, 'original');
    for (let attempt = 0; attempt < 3; attempt++) {
        const result = runStep(publish, 'Prepare PNG-only snapshot commit', temporary);
        assert.equal(result.status, 0, result.stderr);
    }
    assert.doesNotMatch(fs.readFileSync(path.join(temporary.directory, 'git-calls'), 'utf8'), /commit/);
});

test('publisher preserves screenshot filenames with spaces', (t) => {
    const temporary = fixture(t);
    const relativePath = 'e2e/cart/cartPage.cy.ts/2-2-5 cart with promo code.png';
    temporary.write(`snapshots/${relativePath}`, 'generated');
    const result = runStep(publish, 'Prepare PNG-only snapshot commit', temporary, { TEST_DIFF_STATUS: '1' });
    assert.equal(result.status, 0, result.stderr);
    assert.equal(
        fs.readFileSync(
            path.join(temporary.directory, 'project-base/storefront/cypress/snapshots', relativePath),
            'utf8',
        ),
        'generated',
    );
});

for (const invalidPath of ['snapshots/e2e/cart/execute.sh', 'snapshots/outside.png']) {
    test(`publisher rejects unexpected artifact file ${invalidPath}`, (t) => {
        const temporary = fixture(t);
        temporary.write('snapshots/e2e/cart/example.png', 'generated');
        temporary.write(invalidPath, 'not a reference');
        const result = runStep(publish, 'Prepare PNG-only snapshot commit', temporary);
        assert.notEqual(result.status, 0);
        assert.equal(fs.existsSync(path.join(temporary.directory, 'git-calls')), false);
    });
}

test('publisher rejects an artifact symlink', (t) => {
    const temporary = fixture(t);
    const outside = temporary.write('outside', 'keep');
    temporary.write('snapshots/e2e/cart/example.png', 'generated');
    fs.symlinkSync(outside, path.join(temporary.directory, 'snapshots/e2e/cart/link.png'));
    const result = runStep(publish, 'Prepare PNG-only snapshot commit', temporary);
    assert.notEqual(result.status, 0);
    assert.equal(fs.existsSync(path.join(temporary.directory, 'git-calls')), false);
});

for (const directoryLink of [false, true]) {
    test(`publisher rejects a PR-controlled ${directoryLink ? 'directory' : 'file'} symlink destination`, (t) => {
        const temporary = fixture(t);
        temporary.write('snapshots/e2e/cart/example.png', 'generated');
        const outside = temporary.write('outside/example.png', 'keep');
        const target = path.join(temporary.directory, directoryLink ? path.dirname(referencePath) : referencePath);
        fs.mkdirSync(path.dirname(target), { recursive: true });
        fs.symlinkSync(directoryLink ? path.dirname(outside) : outside, target);
        const result = runStep(publish, 'Prepare PNG-only snapshot commit', temporary);
        assert.notEqual(result.status, 0);
        assert.equal(fs.readFileSync(outside, 'utf8'), 'keep');
        assert.equal(fs.existsSync(path.join(temporary.directory, 'git-calls')), false);
    });
}

test('publisher rejects an empty artifact', (t) => {
    const temporary = fixture(t);
    temporary.write('snapshots/.keep', '');
    const result = runStep(publish, 'Prepare PNG-only snapshot commit', temporary);
    assert.notEqual(result.status, 0);
    assert.equal(fs.existsSync(path.join(temporary.directory, 'git-calls')), false);
});
