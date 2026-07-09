import { render, screen, waitFor } from '@testing-library/react';
import { StoresWrapper } from 'components/Blocks/StoreList/StoresWrapper';
import { TIDs } from 'cypress/tids';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { type ReactNode } from 'react';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const testState = vi.hoisted(() => ({
    defaultCoordinates: null as { latitude: number; longitude: number } | null,
    updateCoordinates: vi.fn(),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        lang: 'en',
        t: (key: string) => key,
    }),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: unknown) => unknown) =>
        selector({
            coordinates: testState.defaultCoordinates,
            updateCoordinates: testState.updateCoordinates,
        }),
}));

vi.mock('components/Basic/GoogleMap/GoogleMap', () => ({
    GoogleMap: () => <div data-testid="google-map" />,
}));

vi.mock('react-infinite-scroll-component', () => ({
    default: ({ children }: { children: ReactNode }) => <div>{children}</div>,
}));

const storesWithoutResults: TypeListedStoreConnectionFragment = {
    __typename: 'StoreConnection',
    searchCoordinates: null,
    pageInfo: {
        __typename: 'PageInfo',
        hasNextPage: false,
        endCursor: null,
    },
    edges: [],
};

describe('StoresWrapper layout states', () => {
    beforeEach(() => {
        testState.defaultCoordinates = null;
        testState.updateCoordinates.mockClear();
    });

    test('keeps the search and map layout visible in initial error state', () => {
        const { container } = render(
            <StoresWrapper
                appliedSearchTextValue=""
                isDistanceFromSearchText={false}
                searchTextValue=""
                storeConnectionErrorMessage="Stores could not be loaded. Please try again later."
                stores={null}
                onSearchTextCallback={vi.fn()}
            />,
        );

        expect(screen.getByRole('searchbox', { name: 'City or postcode' })).toBeInTheDocument();
        expect(screen.getByRole('alert')).toHaveTextContent('Stores could not be loaded. Please try again later.');
        expect(container.querySelector(`[data-tid="${TIDs.stores_map}"]`)).toBeInTheDocument();
    });

    test('shows an empty result message without removing the map layout', () => {
        render(
            <StoresWrapper
                appliedSearchTextValue="unknown city"
                isDistanceFromSearchText
                searchTextValue="unknown city"
                stores={storesWithoutResults}
                onSearchTextCallback={vi.fn()}
            />,
        );

        expect(screen.getByRole('searchbox', { name: 'City or postcode' })).toBeInTheDocument();
        expect(screen.getByText('No stores found')).toBeInTheDocument();
        expect(screen.getByText('Try changing the city or postcode.')).toBeInTheDocument();
        expect(screen.getByTestId('google-map')).toBeInTheDocument();
    });

    test('passes browser geolocation coordinates to the store query callback', async () => {
        const coordinates = { latitude: 50.087, longitude: 14.421 };
        const onUserCoordinatesCallback = vi.fn();
        const originalGeolocation = navigator.geolocation;

        Object.defineProperty(navigator, 'geolocation', {
            configurable: true,
            value: {
                getCurrentPosition: vi.fn((success: PositionCallback) => {
                    success({
                        coords: coordinates,
                    } as GeolocationPosition);
                }),
            },
        });

        render(
            <StoresWrapper
                appliedSearchTextValue=""
                isDistanceFromSearchText={false}
                searchTextValue=""
                stores={storesWithoutResults}
                onSearchTextCallback={vi.fn()}
                onUserCoordinatesCallback={onUserCoordinatesCallback}
            />,
        );

        await waitFor(() => {
            expect(onUserCoordinatesCallback).toHaveBeenCalledWith(coordinates);
        });
        expect(testState.updateCoordinates).toHaveBeenCalledWith(coordinates);

        Object.defineProperty(navigator, 'geolocation', {
            configurable: true,
            value: originalGeolocation,
        });
    });
});
