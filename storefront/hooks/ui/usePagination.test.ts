import { usePagination } from './usePagination';
import { expect, test } from '@jest/globals';
import { renderHook } from '@testing-library/react-hooks';
import 'jest-styled-components';

test('test pagination numbers', async () => {
    expect(renderHook(() => usePagination(59, 3, false, 9)).result.current).toStrictEqual([1, 2, 3, 4, 5, 6, 7]);
    expect(renderHook(() => usePagination(18, 1, true, 5)).result.current).toStrictEqual([1, 2, 3, 4]);
    expect(renderHook(() => usePagination(188, 1, false, 5)).result.current).toStrictEqual([1, 2, 3, 4, 5, 38]);
    expect(renderHook(() => usePagination(188, 1, true, 5)).result.current).toStrictEqual([1, 2, 3, 4, 38]);
    expect(renderHook(() => usePagination(196, 17, false, 5)).result.current).toStrictEqual([1, 16, 17, 18, 40]);
    expect(renderHook(() => usePagination(196, 17, true, 5)).result.current).toStrictEqual([1, 16, 17, 18, 40]);
    expect(renderHook(() => usePagination(196, 39, false, 5)).result.current).toStrictEqual([1, 36, 37, 38, 39, 40]);
    expect(renderHook(() => usePagination(196, 39, true, 5)).result.current).toStrictEqual([1, 37, 38, 39, 40]);
    expect(renderHook(() => usePagination(79, 4, false, 9)).result.current).toStrictEqual([1, 2, 3, 4, 5, 9]);
    expect(renderHook(() => usePagination(79, 5, false, 9)).result.current).toStrictEqual([1, 4, 5, 6, 9]);
});
