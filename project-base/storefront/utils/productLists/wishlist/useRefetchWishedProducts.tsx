import {
    ProductListQueryDocument,
    TypeProductListQuery,
    TypeProductListQueryVariables,
} from 'graphql/requests/productLists/queries/ProductListQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { useRef } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useClient } from 'urql';
import { useBroadcastChannel } from 'utils/useBroadcastChannel';

export const useRefetchWishedProducts = () => {
    const client = useClient();
    const wishlistUuid = usePersistStore((s) => s.productListUuids.WISHLIST);
    const wishlistUuidRef = useRef(wishlistUuid);
    wishlistUuidRef.current = wishlistUuid;

    useBroadcastChannel('refetchWishedProducts', async () => {
        if (wishlistUuidRef.current) {
            await client
                .query<TypeProductListQuery, TypeProductListQueryVariables>(
                    ProductListQueryDocument,
                    {
                        input: { type: TypeProductListTypeEnum.Wishlist, uuid: wishlistUuidRef.current },
                    },
                    { requestPolicy: 'network-only' },
                )
                .toPromise();
        }
    });
};
