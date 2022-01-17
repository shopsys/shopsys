import 'jest-styled-components';
import { expect, test } from '@jest/globals';
import { usePagination } from './usePagination';

test('test pagination numbers', async () => {
    expect(usePagination(59, 3, false, 9)).toStrictEqual([1, 2, 3, 4, 5, 6, 7]);
    expect(usePagination(18, 1, true, 5)).toStrictEqual([1, 2, 3, 4]);
    expect(usePagination(188, 1, false, 5)).toStrictEqual([1, 2, 3, 4, 5, 38]);
    expect(usePagination(188, 1, true, 5)).toStrictEqual([1, 2, 3, 4, 38]);
    expect(usePagination(196, 17, false, 5)).toStrictEqual([1, 16, 17, 18, 40]);
    expect(usePagination(196, 17, true, 5)).toStrictEqual([1, 16, 17, 18, 40]);
    expect(usePagination(196, 39, false, 5)).toStrictEqual([1, 36, 37, 38, 39, 40]);
    expect(usePagination(196, 39, true, 5)).toStrictEqual([1, 37, 38, 39, 40]);
    expect(usePagination(79, 4, false, 9)).toStrictEqual([1, 2, 3, 4, 5, 9]);
    expect(usePagination(79, 5, false, 9)).toStrictEqual([1, 4, 5, 6, 9]);
});
