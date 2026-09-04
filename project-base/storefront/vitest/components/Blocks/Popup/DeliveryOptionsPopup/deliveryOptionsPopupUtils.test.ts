import { DeliveryOptionsProduct } from 'components/Blocks/Popup/DeliveryOptionsPopup/deliveryOptionsPopupTypes';
import {
    getDeliveryOptionsContentState,
    getProductDeliveryStoresConnectionFromData,
} from 'components/Blocks/Popup/DeliveryOptionsPopup/deliveryOptionsPopupUtils';
import { TypeProductDeliveryOptionsQuery } from 'graphql/requests/transports/queries/ProductDeliveryOptionsQuery.generated';
import { TypeProductDeliveryStoresQuery } from 'graphql/requests/transports/queries/ProductDeliveryStoresQuery.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { CombinedError } from 'urql';
import { describe, expect, test } from 'vitest';

const selectedProduct = {
    uuid: '00000000-0000-0000-0000-000000000001',
    fullName: 'Test product',
    availability: { name: 'In stock', status: TypeAvailabilityStatusEnum.InStock },
    price: { priceWithVat: '100' },
} as DeliveryOptionsProduct;

const emptyDeliveryOptionsData = { productDeliveryOptions: [] } as TypeProductDeliveryOptionsQuery;
const loadedDeliveryOptionsData = { productDeliveryOptions: [{}] } as unknown as TypeProductDeliveryOptionsQuery;

describe('deliveryOptionsPopupUtils', () => {
    test.each([
        {
            name: 'no product is selected',
            product: null,
            isFetching: false,
            error: undefined,
            data: undefined,
            expectedState: 'no-product',
        },
        {
            name: 'the query failed',
            product: selectedProduct,
            isFetching: false,
            error: new CombinedError({ networkError: new Error('Query failed') }),
            data: undefined,
            expectedState: 'error',
        },
        {
            name: 'the query is fetching',
            product: selectedProduct,
            isFetching: true,
            error: undefined,
            data: undefined,
            expectedState: 'loading',
        },
        {
            name: 'the query has not returned data yet',
            product: selectedProduct,
            isFetching: false,
            error: undefined,
            data: undefined,
            expectedState: 'loading',
        },
        {
            name: 'the query returned no delivery options',
            product: selectedProduct,
            isFetching: false,
            error: undefined,
            data: emptyDeliveryOptionsData,
            expectedState: 'empty',
        },
        {
            name: 'the query returned delivery options',
            product: selectedProduct,
            isFetching: false,
            error: undefined,
            data: loadedDeliveryOptionsData,
            expectedState: 'loaded',
        },
    ])('returns $expectedState when $name', ({ product, isFetching, error, data, expectedState }) => {
        const contentState = getDeliveryOptionsContentState(product, isFetching, error, data);

        expect(contentState.type).toBe(expectedState);
    });

    test('maps product delivery stores to the shared store connection shape', () => {
        const productDeliveryStoresData = {
            productDeliveryStores: {
                __typename: 'ProductDeliveryStoreConnection',
                searchCoordinates: null,
                pageInfo: { hasNextPage: false, endCursor: null },
                edges: [
                    {
                        __typename: 'ProductDeliveryStoreEdge',
                        node: {
                            __typename: 'ProductDeliveryStore',
                            expectedDeliveryDate: '2026-08-28',
                            store: { __typename: 'Store', identifier: 'store-1', name: 'Test store' },
                        },
                    },
                ],
            },
        } as unknown as TypeProductDeliveryStoresQuery;

        const storeConnection = getProductDeliveryStoresConnectionFromData(productDeliveryStoresData);

        expect(storeConnection).toMatchObject({
            __typename: 'StoreConnection',
            edges: [
                {
                    __typename: 'StoreEdge',
                    node: {
                        identifier: 'store-1',
                        name: 'Test store',
                        expectedDeliveryDate: '2026-08-28',
                    },
                },
            ],
        });
    });
});
