import parseLangFromFileName from './parseLangFromFileName';

test.each([
    ['', undefined],
    ['lang', undefined],
    ['lang.po', 'lang'],
    ['lang.en.po', 'en'],
    ['lang.js', 'lang'],
    ['lang.php', 'lang']
])(
    'parseLangFromFileName test',
    (filePath, expected) => {
        expect(parseLangFromFileName(filePath)).toStrictEqual(expected);
    }
);
