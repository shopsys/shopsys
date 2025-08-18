import getCliParameters from './getCliParameters';

test.each([
    [[], '', []],
    [['env=development'], 'env', ['development']],
    [['env=development'], 'missing', []],
    [['env=dev', 'env=prod', 'mode=build'], 'env', ['dev', 'prod']],

    // Real CLI scenarios
    [['--config=webpack.config.js', '--mode=production'], '--config', ['webpack.config.js']],
    [['--output=/path/to/dist', '--verbose'], '--output', ['/path/to/dist']],
    [['node', 'script.js', '--port=3000'], '--port', ['3000']],

    // Edge cases
    [['env='], 'env', ['']], // Empty value
    [['env=prod=backup'], 'env', ['prod=backup']], // Value contains equals
    [['env=http://localhost:3000'], 'env', ['http://localhost:3000']], // URL with colons
    [['env'], 'env', []], // Parameter without equals (flag)
    [['environment=prod'], 'env', []], // Partial key match should fail
    [['env=dev', 'envvar=test'], 'env', ['dev']], // Exact match only

    // Complex scenarios
    [['--file=package.json', '--file=tsconfig.json', '--watch'], '--file', ['package.json', 'tsconfig.json']],
    [['build', '--target=es2020', '--outDir=./dist', '--sourcemap'], '--target', ['es2020']],
    [['--db-url=postgresql://user:pass@localhost/db'], '--db-url', ['postgresql://user:pass@localhost/db']],
])('getCliParameters test', (parameters, parameterName, expected) => {
    expect(getCliParameters(parameters, parameterName)).toStrictEqual(expected);
});
