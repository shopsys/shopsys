module.exports = {
    createOldCatalogs: false,
    defaultNamespace: 'common',
    indentation: 4,
    keepRemoved: false,
    useKeysAsDefaultValue: (locale) => locale === 'en',
    functions: ['t'],
    lexers: {
        jsx: [
            {
                lexer: 'JsxLexer',
                attr: 'i18nKey',
            },
        ],
        default: [
            {
                lexer: 'JsxLexer',
                attr: 'i18nKey',
            },
        ],
    },
    lineEnding: 'lf',
    locales: ['en', 'cs', 'sk'],
    output: 'public/locales/$LOCALE/$NAMESPACE.json',
    namespaceSeparator: false,
    keySeparator: false,
    contextSeparator: false,
    pluralSeparator: '_',
    input: [
        '../components/**/*.{ts,tsx}',
        '../connectors/**/*.{ts,tsx}',
        '../gtm/**/*.{ts,tsx}',
        '../pages/**/*.{ts,tsx}',
        '../store/**/*.{ts,tsx}',
        '../utils/**/*.{ts,tsx}',
    ],
    sort: true,
    verbose: true,
};
