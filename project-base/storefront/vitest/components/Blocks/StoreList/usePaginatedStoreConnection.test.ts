import { act, renderHook } from '@testing-library/react';
import { usePaginatedStoreConnection } from 'components/Blocks/StoreList/usePaginatedStoreConnection';
import { DocumentNode } from 'graphql';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { TypeCoordinates } from 'graphql/types';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const KNOWN_COORDINATES: TypeCoordinates = { latitude: 50, longitude: 14 };

const testState = vi.hoisted(() => ({
    defaultCoordinates: { latitude: 50, longitude: 14 } as { latitude: number; longitude: number } | null,
    queryVariables: [] as Array<Record<string, unknown>>,
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: unknown) => unknown) =>
        selector({
            coordinates: testState.defaultCoordinates,
            updateCoordinates: (coordinates: { latitude: number; longitude: number } | null) => {
                testState.defaultCoordinates = coordinates;
            },
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

const mockBrowserGeolocation = (coordinates: TypeCoordinates | null) => {
    const originalGeolocation = navigator.geolocation;

    Object.defineProperty(navigator, 'geolocation', {
        configurable: true,
        value:
            coordinates === null
                ? undefined
                : {
                      getCurrentPosition: vi.fn((success: PositionCallback) => {
                          success({ coords: coordinates } as GeolocationPosition);
                      }),
                  },
    });

    return () => {
        Object.defineProperty(navigator, 'geolocation', {
            configurable: true,
            value: originalGeolocation,
        });
    };
};

describe('usePaginatedStoreConnection', () => {
    beforeEach(() => {
        testState.queryVariables = [];
        testState.defaultCoordinates = KNOWN_COORDINATES;
    });

    test('uses default user coordinates when there is no search text', () => {
        renderStoreConnectionHook();

        expect(getLatestQueryVariables()).toMatchObject({
            searchText: null,
            coordinates: KNOWN_COORDINATES,
        });
    });

    test('asks the browser for the coordinates when the session does not know them yet', () => {
        testState.defaultCoordinates = null;
        const browserCoordinates: TypeCoordinates = { latitude: 49.2, longitude: 16.6 };
        const restoreGeolocation = mockBrowserGeolocation(browserCoordinates);

        const { rerender } = renderStoreConnectionHook();
        rerender();

        expect(testState.defaultCoordinates).toEqual(browserCoordinates);
        expect(getLatestQueryVariables()).toMatchObject({ coordinates: browserCoordinates });

        restoreGeolocation();
    });

    test('queries without coordinates when the browser cannot provide them', () => {
        testState.defaultCoordinates = null;
        const restoreGeolocation = mockBrowserGeolocation(null);

        renderStoreConnectionHook();

        expect(getLatestQueryVariables()).toMatchObject({ coordinates: null });

        restoreGeolocation();
    });

    test('switches to the shared coordinates once they become known', () => {
        testState.defaultCoordinates = null;
        const restoreGeolocation = mockBrowserGeolocation(null);

        const { rerender } = renderStoreConnectionHook();
        expect(getLatestQueryVariables()).toMatchObject({ coordinates: null });

        testState.defaultCoordinates = KNOWN_COORDINATES;
        rerender();

        expect(getLatestQueryVariables()).toMatchObject({ coordinates: KNOWN_COORDINATES });

        restoreGeolocation();
    });

    test('keeps locally overridden coordinates instead of the shared ones', () => {
        const overriddenCoordinates: TypeCoordinates = { latitude: 48.1, longitude: 17.1 };
        const { result } = renderStoreConnectionHook();

        act(() => result.current.setUserCoordinates(overriddenCoordinates));

        expect(getLatestQueryVariables()).toMatchObject({ coordinates: overriddenCoordinates });
    });

    test('keeps the local override even when the shared coordinates arrive later', () => {
        testState.defaultCoordinates = null;
        const restoreGeolocation = mockBrowserGeolocation(null);
        const overriddenCoordinates: TypeCoordinates = { latitude: 48.1, longitude: 17.1 };
        const { result, rerender } = renderStoreConnectionHook();

        act(() => result.current.setUserCoordinates(overriddenCoordinates));
        testState.defaultCoordinates = KNOWN_COORDINATES;
        rerender();

        expect(getLatestQueryVariables()).toMatchObject({ coordinates: overriddenCoordinates });

        restoreGeolocation();
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
