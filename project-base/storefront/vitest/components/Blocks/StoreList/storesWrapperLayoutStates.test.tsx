import { render, screen } from '@testing-library/react';
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

    test('offsets the sticky map below the fixed header on the stores page only', () => {
        const { container, unmount } = render(
            <StoresWrapper
                appliedSearchTextValue=""
                isDistanceFromSearchText={false}
                searchTextValue=""
                stores={storesWithoutResults}
                onSearchTextCallback={vi.fn()}
            />,
        );

        expect(container.querySelector(`[data-tid="${TIDs.stores_map}"]`)?.firstElementChild).toHaveClass(
            'vl:top-[calc(var(--sticky-navigation-offset,0px)+var(--spacing-5))]',
        );

        unmount();

        const { container: pickupSelectionContainer } = render(
            <StoresWrapper
                appliedSearchTextValue=""
                isDistanceFromSearchText={false}
                searchTextValue=""
                stores={storesWithoutResults}
                variant="pickupSelection"
                onSearchTextCallback={vi.fn()}
            />,
        );

        expect(
            pickupSelectionContainer.querySelector(`[data-tid="${TIDs.stores_map}"]`)?.firstElementChild,
        ).toHaveClass('vl:top-0');
    });
});
