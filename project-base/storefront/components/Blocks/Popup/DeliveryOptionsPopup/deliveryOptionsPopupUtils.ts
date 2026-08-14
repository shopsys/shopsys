import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { TypeProductDeliveryOptionsQuery } from 'graphql/requests/transports/queries/ProductDeliveryOptionsQuery.generated';
import { TypeProductDeliveryStoresQuery } from 'graphql/requests/transports/queries/ProductDeliveryStoresQuery.generated';
import { CombinedError } from 'urql';
import { DeliveryOptionsProduct } from './deliveryOptionsPopupTypes';

type DeliveryOptionsContentState =
    | { type: 'no-product' }
    | { type: 'error' }
    | { type: 'loading' }
    | { type: 'empty' }
    | { type: 'loaded'; selectedProduct: DeliveryOptionsProduct };

export const getDeliveryOptionsContentState = (
    selectedProduct: DeliveryOptionsProduct | null,
    isFetchingDeliveryOptions: boolean,
    deliveryOptionsError: CombinedError | undefined,
    productDeliveryOptionsData: TypeProductDeliveryOptionsQuery | undefined,
): DeliveryOptionsContentState => {
    if (selectedProduct === null) {
        return { type: 'no-product' };
    }

    if (deliveryOptionsError !== undefined) {
        return { type: 'error' };
    }

    if (isFetchingDeliveryOptions || productDeliveryOptionsData === undefined) {
        return { type: 'loading' };
    }

    if (productDeliveryOptionsData.productDeliveryOptions.length === 0) {
        return { type: 'empty' };
    }

    return { type: 'loaded', selectedProduct };
};

export const getProductDeliveryStoresConnectionFromData = (
    data: TypeProductDeliveryStoresQuery | undefined,
): TypeListedStoreConnectionFragment | null | undefined => {
    const storeConnection = data?.productDeliveryStores;

    if (!storeConnection) {
        return storeConnection;
    }

    // flatten the delivery store nodes into the store connection shape consumed by the shared store list components
    return {
        ...storeConnection,
        __typename: 'StoreConnection',
        edges:
            storeConnection.edges?.map((edge) =>
                edge?.node
                    ? {
                          __typename: 'StoreEdge' as const,
                          node: { ...edge.node.store, expectedDeliveryDate: edge.node.expectedDeliveryDate },
                      }
                    : null,
            ) ?? null,
    };
};
