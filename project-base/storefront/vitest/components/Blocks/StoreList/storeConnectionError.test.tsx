import { render, screen } from '@testing-library/react';
import { PickupPlacePopup } from 'components/Blocks/Popup/PickupPlacePopup';
import { StoresQueryDocument } from 'graphql/requests/stores/queries/StoresQuery.generated';
import { TransportStoresQueryDocument } from 'graphql/requests/transports/queries/TransportStoresQuery.generated';
import StoresPage from 'pages/stores';
import { type ReactNode } from 'react';
import { CombinedError } from 'urql';
import { type ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

const { queryResultMock, sessionStoreState, updatePortalContentMock } = vi.hoisted(() => ({
    queryResultMock: vi.fn(),
    sessionStoreState: {
        coordinates: null,
    },
    updatePortalContentMock: vi.fn(),
}));

vi.mock('urql', async (importOriginal) => {
    const actual = await importOriginal<typeof import('urql')>();

    return {
        ...actual,
        useClient: () => ({
            query: vi.fn(),
        }),
        useQuery: (args: unknown) => [queryResultMock(args)],
    };
});

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        lang: 'en',
        t: (key: string) => key,
    }),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: unknown) => unknown) =>
        selector({
            ...sessionStoreState,
            updatePortalContent: updatePortalContentMock,
        }),
}));

vi.mock('components/Layout/CommonLayout', () => ({
    CommonLayout: ({ children }: { children: ReactNode }) => <main>{children}</main>,
}));

vi.mock('components/Layout/Popup/Popup', () => ({
    Popup: ({ children, title }: { children: ReactNode; title: string }) => (
        <section aria-label={title}>{children}</section>
    ),
}));

vi.mock('components/Blocks/StoreList/StoresWrapper', () => ({
    StoresWrapper: () => <div data-testid="stores-wrapper" />,
}));

vi.mock('gtm/factories/useGtmStaticPageReadyEvent', () => ({
    useGtmStaticPageReadyEvent: () => ({}),
}));

vi.mock('gtm/utils/pageReadyEvents/useGtmPageReadyEvent', () => ({
    useGtmPageReadyEvent: vi.fn(),
}));

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({
        pickupPlace: null,
    }),
}));

const storeConnectionErrorMessage = 'Stores could not be loaded. Please try again later.';
const storesPageProps: ServerSidePropsType = {
    urqlState: {},
    isMaintenance: false,
    isForbidden: false,
    customerUserRoles: [],
    domainConfig: defaultTestDomainConfig,
    cookiesStore: {
        lastVisitedProductsCatnums: null,
        userIdentifier: 'test-user-identifier',
        isUserSnapEnabled: false,
    },
};

describe('store connection error handling', () => {
    beforeEach(() => {
        queryResultMock.mockReturnValue({
            data: undefined,
            fetching: false,
            error: new CombinedError({
                graphQLErrors: [{ message: 'Store connection failed' }],
            }),
        });
    });

    test('shows an error state when the stores query fails', () => {
        render(<StoresPage {...storesPageProps} />);

        expect(queryResultMock).toHaveBeenCalledWith(expect.objectContaining({ query: StoresQueryDocument }));
        expect(screen.getByRole('alert')).toHaveTextContent(storeConnectionErrorMessage);
        expect(screen.queryByTestId('stores-wrapper')).not.toBeInTheDocument();
    });

    test('shows an error state when the transport stores query fails', () => {
        render(
            <PickupPlacePopup
                lastOrderPickupPlace={null}
                transportUuid="transport-uuid"
                onChangePickupPlaceCallback={vi.fn()}
            />,
        );

        expect(queryResultMock).toHaveBeenCalledWith(expect.objectContaining({ query: TransportStoresQueryDocument }));
        expect(screen.getByRole('alert')).toHaveTextContent(storeConnectionErrorMessage);
        expect(screen.queryByTestId('stores-wrapper')).not.toBeInTheDocument();
    });
});
