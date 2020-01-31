import findLang from './findLang';

test.each([
    ['', undefined],
    ['lang', undefined],
    ['lang.po', 'lang'],
    ['lang.en.po', 'en'],
    ['lang.js', 'lang'],
    ['lang.php', 'lang']
])(
    'findLang test',
    (filePath, expected) => {
        expect(findLang(filePath)).toStrictEqual(expected);
    }
);
