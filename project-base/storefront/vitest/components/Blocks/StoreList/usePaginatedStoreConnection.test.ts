import { act, renderHook } from '@testing-library/react';
import { usePaginatedStoreConnection } from 'components/Blocks/StoreList/usePaginatedStoreConnection';
import { DocumentNode } from 'graphql';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { TypeCoordinates } from 'graphql/types';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const testState = vi.hoisted(() => ({
    defaultCoordinates: { latitude: 50, longitude: 14 },
    queryVariables: [] as Array<Record<string, unknown>>,
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: unknown) => unknown) =>
        selector({
            coordinates: testState.defaultCoordinates,
            updateCoordinates: vi.fn(),
        }),
}));

vi.mock('urql', () => ({
    useClient: () => ({
        query: vi.fn(() => ({
            toPromise: vi.fn(async () => ({ data: undefined })),
        })),
    }),
    useQuery: ({ variables }: { variables: Record<string, unknown> }) => {
        testState.queryVariables.push(variables);

        return [{ data: undefined, fetching: false, error: undefined }];
    },
}));

vi.mock('utils/useDebounce', () => ({
    useDebounce: <TValue>(value: TValue) => value,
}));

const renderStoreConnectionHook = () =>
    renderHook(() =>
        usePaginatedStoreConnection<{ stores: TypeListedStoreConnectionFragment | null }>({
            queryDocument: {} as DocumentNode,
            getStoreConnectionFromData: (data) => data?.stores,
        }),
    );

const getLatestQueryVariables = () => testState.queryVariables.at(-1);

describe('usePaginatedStoreConnection', () => {
    beforeEach(() => {
        testState.queryVariables = [];
    });

    test('uses default user coordinates when there is no search text', () => {
        renderStoreConnectionHook();

        expect(getLatestQueryVariables()).toMatchObject({
            searchText: null,
            coordinates: testState.defaultCoordinates as TypeCoordinates,
        });
    });

    test('does not send user coordinates while searching by text', () => {
        const { result } = renderStoreConnectionHook();

        act(() => result.current.setSearchTextValue('asdfasdfasdf'));

        expect(getLatestQueryVariables()).toMatchObject({
            searchText: 'asdfasdfasdf',
            coordinates: null,
        });
    });
});
