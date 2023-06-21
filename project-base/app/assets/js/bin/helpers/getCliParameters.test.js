import getCliParameters from './getCliParameters';

test.each([
    [[], '', []],
    [['a=a'], 'a', ['a']],
    [['a=a'], 'b', []],
    [['a=a', 'a=b', 'b=c'], 'a', ['a', 'b']]
])(
    'getCliParameters test',
    (parameters, parameterName, expected) => {
        expect(getCliParameters(parameters, parameterName)).toStrictEqual(expected);
    }
);
