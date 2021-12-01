import parseFile from './parseFile';

test('class is correctly parse', () => {
    const mockPath = './assets/js/commands/translations/mocks/DummyTranslations.js';
    const translations = parseFile(mockPath);

    const expectdArray = [
        {
            'domain': undefined,
            'id': 'trans in constructor',
            'line': 5,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': '{0}transChoice in constructor{1}transChoice in constructor',
            'line': 6,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': 'trans in method',
            'line': 10,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': '{0}transChoice in method{1}transChoice in method',
            'line': 11,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': 'trans in callback in method',
            'line': 14,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': '{0}transChoice in callback in method{1}transChoice in callback in method',
            'line': 15,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': 'trans in static method',
            'line': 22,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': '{0}transChoice in static method{1}transChoice in static method',
            'line': 23,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': 'trans in callback in static method',
            'line': 26,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': '{0}transChoice in callback in static method{1}transChoice in callback in static method',
            'line': 27,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': 'trans out of class',
            'line': 34,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': '{0}transChoice out of class{1}transChoice out of class',
            'line': 35,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': 'trans in simple function',
            'line': 38,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        },
        {
            'domain': undefined,
            'id': '{0}transChoice in simple function{1}transChoice in simple function',
            'line': 39,
            'locale': undefined,
            'source': './assets/js/commands/translations/mocks/DummyTranslations.js'
        }
    ];

    expect(translations).toHaveLength(14);
    expect(translations).toEqual(expectdArray);
});

test('class has syntax error, throw exception', () => {
    const mockPath = './assets/js/commands/translations/mocks/DummyClassWithError.js';

    try {
        parseFile(mockPath);
    } catch (syntaxErrorException) {
        expect(syntaxErrorException).toBeInstanceOf(SyntaxError);
    }

});
