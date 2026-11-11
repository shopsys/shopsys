import { renderHook } from '@testing-library/react';
import { gql } from 'urql';
import { useProductListQueryResult } from 'utils/productLists/useProductListQueryResult';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const state = vi.hoisted(() => ({ result: {} as Record<string, unknown> }));

vi.mock('urql', async (importOriginal) => ({
    ...(await importOriginal<typeof import('urql')>()),
    useQuery: () => [state.result, vi.fn()],
}));

const query = gql`
    query ProductListTestQuery($input: String!) {
        test(input: $input)
    }
`;
const variables = { input: 'list' };

const operation = async () => {
    const { createRequest } = await import('urql');
    return {
        key: createRequest(query, variables).key,
        context: { productListSelection: 'selection', productListContent: '' },
    };
};

const renderResult = () =>
    renderHook(() => useProductListQueryResult({ query, variables, pause: false, selectionKey: 'selection' }));

describe('useProductListQueryResult', () => {
    beforeEach(async () => {
        state.result = { operation: await operation(), data: { order: ['a', 'b'] }, stale: false, fetching: false };
    });

    test('shows the latest cache content while it is being refreshed', () => {
        const { result, rerender } = renderResult();
        expect(result.current.data).toEqual({ order: ['a', 'b'] });

        state.result = { ...state.result, data: { order: ['b', 'a'] }, stale: true };
        rerender();

        expect(result.current.data).toEqual({ order: ['b', 'a'] });
    });

    test('keeps the confirmed content when a refresh fails', () => {
        const { result, rerender } = renderResult();

        state.result = { ...state.result, data: undefined, error: new Error('Network unavailable') };
        rerender();

        expect(result.current.data).toEqual({ order: ['a', 'b'] });
    });
});
