import { act, renderHook } from '@testing-library/react';
import { getOperationAST, parse } from 'graphql';
import { ChangePaymentInCartMutationDocument } from 'graphql/requests/cart/mutations/ChangePaymentInCartMutation.generated';
import { CartQueryDocument } from 'graphql/requests/cart/queries/CartQuery.generated';
import {
    CreateOrderMutationDocument,
    TypeCreateOrderMutationVariables,
} from 'graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { ReactNode } from 'react';
import { useSessionStore } from 'store/useSessionStore';
// biome-ignore lint/style/noRestrictedImports: Exercise production Graphcache with a controlled network transport.
import { createClient, fetchExchange, Provider } from 'urql';
import { cache } from 'urql/cache/cacheExchange';
import { cartModificationsExchange } from 'urql/cartModificationsExchange';
import { dedupExchange } from 'urql/dedupExchange';
import { useCartModificationsSideEffects } from 'utils/cart/useCartModificationsSideEffects';
import { expect, test, vi } from 'vitest';
import { getCartModifications, getCartResponse, paymentResponseFixture } from 'vitest/helpers/cartModificationsFixture';

const { showMessageEventMock } = vi.hoisted(() => ({ showMessageEventMock: vi.fn() }));

vi.mock('gtm/handlers/onGtmShowMessageEventHandler', () => ({ onGtmShowMessageEventHandler: showMessageEventMock }));
vi.mock('gtm/handlers/onGtmPaymentChangeEventHandler', () => ({ onGtmPaymentChangeEventHandler: vi.fn() }));
vi.mock('gtm/utils/useGtmCartInfo', () => ({ useGtmCartInfo: () => ({ gtmCartInfo: null }) }));
vi.mock('components/providers/AuthorizationProvider', () => ({ useAuthorization: () => ({ canSeePrices: true }) }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: unknown) => unknown) => selector({ cartUuid: null }),
}));

const unavailableTransport = getCartModifications({ transportModifications: { transportUnavailable: true } });

const getResponseData = (operationName: string) => {
    if (operationName === 'CartQuery') {
        return {
            __typename: 'Query',
            cart: getCartResponse(getCartModifications(), { payment: paymentResponseFixture }),
        };
    }

    if (operationName === 'CreateOrderMutation') {
        return {
            CreateOrder: {
                __typename: 'CreateOrderResult',
                orderCreated: false,
                order: null,
                cart: getCartResponse(unavailableTransport, { payment: paymentResponseFixture }),
            },
        };
    }

    return { ChangePaymentInCart: getCartResponse(getCartModifications()) };
};

test('a refused order reports its modifications once and repeated cached modifications do not reopen them', async () => {
    const requestedOperations: string[] = [];
    const requestedPaymentChanges: (string | null)[] = [];
    let releasePaymentReset = (): void => undefined;
    const paymentResetReleased = new Promise<void>((resolve) => {
        releasePaymentReset = resolve;
    });

    // Payment changes wait, so meanwhile the cached cart still repeats the reported modification
    const respondToGraphqlRequest = async (_url: RequestInfo | URL, options?: RequestInit) => {
        const { query, variables } = JSON.parse(String(options?.body));
        const operationName = getOperationAST(parse(query))!.name!.value;
        requestedOperations.push(operationName);

        if (operationName === 'ChangePaymentInCartMutation') {
            requestedPaymentChanges.push(variables.input.paymentUuid);
            await paymentResetReleased;
        }

        return Response.json({ data: getResponseData(operationName) });
    };

    const client = createClient({
        url: 'http://localhost/graphql',
        exchanges: [dedupExchange, cache, cartModificationsExchange, fetchExchange],
        preferGetMethod: false,
        fetch: respondToGraphqlRequest,
    });
    const wrapper = ({ children }: { children: ReactNode }) => <Provider value={client}>{children}</Provider>;
    const readCachedCart = () => client.readQuery(CartQueryDocument, { cartUuid: 'cart-1' })?.data?.cart;
    const getKeptBatchesCount = () => useSessionStore.getState().cartModificationsBatches.length;
    const getPaymentResetsCount = () => requestedPaymentChanges.filter((paymentUuid) => paymentUuid === null).length;
    const changePayment = () =>
        client
            .mutation(ChangePaymentInCartMutationDocument, {
                input: { cartUuid: 'cart-1', paymentUuid: 'cash-on-delivery', paymentGoPayBankSwift: null },
            })
            .toPromise();

    await client.query(CartQueryDocument, { cartUuid: 'cart-1' }, { requestPolicy: 'network-only' }).toPromise();
    renderHook(useCartModificationsSideEffects, { wrapper });

    await act(() => client.mutation(CreateOrderMutationDocument, {} as TypeCreateOrderMutationVariables).toPromise());
    await vi.waitFor(() => expect(getPaymentResetsCount()).toBe(1));

    expect(getKeptBatchesCount()).toBe(1);
    expect(showMessageEventMock).toHaveBeenCalledOnce();

    act(() => useSessionStore.getState().dismissCartModifications());
    await client.query(CartQueryDocument, { cartUuid: 'cart-1' }, { requestPolicy: 'cache-first' }).toPromise();
    const paymentChangeResponse = act(changePayment);
    await vi.waitFor(() => expect(requestedPaymentChanges).toEqual([null, 'cash-on-delivery']));

    expect(readCachedCart()?.modifications.transportModifications.transportUnavailable).toBe(true);
    expect(getKeptBatchesCount()).toBe(0);

    releasePaymentReset();
    await paymentChangeResponse;
    await vi.waitFor(() =>
        expect(readCachedCart()?.modifications.transportModifications.transportUnavailable).toBe(false),
    );

    expect(getKeptBatchesCount()).toBe(0);
    expect(showMessageEventMock).toHaveBeenCalledOnce();
    expect(getPaymentResetsCount()).toBe(1);
});
