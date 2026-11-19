import { CartQueryDocument } from 'graphql/requests/cart/queries/CartQuery.generated';
import { useSessionStore } from 'store/useSessionStore';
// biome-ignore lint/style/noRestrictedImports: Exercise the exchange chain with a controlled network transport.
import { createClient, fetchExchange, gql } from 'urql';
import { cache } from 'urql/cache/cacheExchange';
import { cartModificationsExchange } from 'urql/cartModificationsExchange';
import { dedupExchange } from 'urql/dedupExchange';
import { beforeEach, describe, expect, test } from 'vitest';
import { getCartModifications, getCartResponse, paymentResponseFixture } from 'vitest/helpers/cartModificationsFixture';

const priceChange = getCartModifications({ transportModifications: { transportPriceChanged: true } });
const createTestClient = (exchanges: Parameters<typeof createClient>[0]['exchanges'], getData: () => object) => {
    const requests: string[] = [];
    const respondToGraphqlRequest = async (_url: RequestInfo | URL, options?: RequestInit) => {
        requests.push(String(options?.body));

        return Response.json({ data: getData() });
    };
    const client = createClient({
        url: 'http://localhost/graphql',
        exchanges,
        preferGetMethod: false,
        fetch: respondToGraphqlRequest,
    });

    return { client, requests };
};

const getReceivedBatches = () =>
    useSessionStore.getState().cartModificationsBatches.map(({ cartUuid, modifications, hasPayment }) => ({
        cartUuid,
        transportPriceChanged: modifications.transportModifications.transportPriceChanged,
        hasPayment,
    }));

const AnyCartMutation = gql`
    mutation AnyCartMutation {
        anyCartMutation
    }
`;

const SlowCartQuery = gql`
    query SlowCartQuery {
        slowCart
    }
`;

const CreatedOrderMutation = gql`
    mutation CreatedOrderMutation {
        createdOrder
    }
`;

describe('cartModificationsExchange', () => {
    beforeEach(() => {
        useSessionStore.setState({
            cartModificationsBatches: [],
            unhandledCartModificationsBatches: [],
            isOrderRejectedByCartModifications: false,
        });
    });

    test.each([
        [
            'a root field',
            { ChangeTransportInCart: getCartResponse(priceChange, { payment: paymentResponseFixture }) },
            true,
        ],
        [
            'a field of the root result',
            { CreateOrder: { orderCreated: false, cart: getCartResponse(priceChange) } },
            false,
        ],
    ])('receives modifications of a cart returned as %s', async (_, data, hasPayment) => {
        const { client } = createTestClient([cartModificationsExchange, fetchExchange], () => data);

        await client.mutation(AnyCartMutation, {}).toPromise();

        expect(getReceivedBatches()).toEqual([{ cartUuid: 'cart-1', transportPriceChanged: true, hasPayment }]);
    });

    test('receives modifications of a logged-in customer cart, which has no UUID', async () => {
        const { client } = createTestClient([cartModificationsExchange, fetchExchange], () => ({
            AddToCart: { cart: getCartResponse(priceChange, { uuid: null }) },
        }));

        await client.mutation(AnyCartMutation, {}).toPromise();

        expect(getReceivedBatches()).toEqual([{ cartUuid: null, transportPriceChanged: true, hasPayment: false }]);
    });

    test('ignores a cart response without modifications', async () => {
        const { client } = createTestClient([cartModificationsExchange, fetchExchange], () => ({
            AddToCart: { cart: getCartResponse(getCartModifications()) },
        }));

        await client.mutation(AnyCartMutation, {}).toPromise();

        expect(getReceivedBatches()).toEqual([]);
    });

    test('receives modifications only from network responses, not from cache reads', async () => {
        const { client, requests } = createTestClient(
            [dedupExchange, cache, cartModificationsExchange, fetchExchange],
            () => ({
                __typename: 'Query',
                cart: getCartResponse(priceChange),
            }),
        );
        const queryCart = (requestPolicy: 'cache-first' | 'network-only') =>
            client.query(CartQueryDocument, { cartUuid: 'cart-1' }, { requestPolicy }).toPromise();

        await queryCart('network-only');
        expect(getReceivedBatches()).toHaveLength(1);

        useSessionStore.getState().dismissCartModifications();
        const cachedResult = await queryCart('cache-first');

        expect(cachedResult.data?.cart?.uuid).toBe('cart-1');
        expect(requests).toHaveLength(1);
        expect(getReceivedBatches()).toEqual([]);

        await queryCart('network-only');

        expect(requests).toHaveLength(2);
        expect(getReceivedBatches()).toHaveLength(1);
    });

    test.each([
        ['an anonymous', 'cart-1'],
        ['a logged-in customer', null],
    ])('clears modifications of %s cart once an order is created and ignores its late responses', async (_, uuid) => {
        let releaseSlowCart = (): void => undefined;
        const slowCartReleased = new Promise<void>((resolve) => {
            releaseSlowCart = resolve;
        });
        const respondToGraphqlRequest = async (_url: RequestInfo | URL, options?: RequestInit) => {
            const { operationName } = JSON.parse(String(options?.body));

            if (operationName === 'CreatedOrderMutation') {
                return Response.json({ data: { CreateOrder: { orderCreated: true, order: {}, cart: null } } });
            }

            if (operationName === 'SlowCartQuery') {
                await slowCartReleased;
            }

            return Response.json({ data: { AddToCart: { cart: getCartResponse(priceChange, { uuid }) } } });
        };
        const client = createClient({
            url: 'http://localhost/graphql',
            exchanges: [cartModificationsExchange, fetchExchange],
            preferGetMethod: false,
            fetch: respondToGraphqlRequest,
        });

        await client.mutation(AnyCartMutation, {}).toPromise();
        const slowCartResponse = client.query(SlowCartQuery, {}).toPromise();
        await client.mutation(CreatedOrderMutation, {}).toPromise();

        expect(getReceivedBatches()).toEqual([]);

        releaseSlowCart();
        await slowCartResponse;

        expect(getReceivedBatches()).toEqual([]);

        await client.mutation(AnyCartMutation, {}).toPromise();

        expect(getReceivedBatches()).toEqual([{ cartUuid: uuid, transportPriceChanged: true, hasPayment: false }]);
    });
});
