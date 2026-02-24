import {
    ProductListQueryDocument,
    TypeProductListQuery,
    TypeProductListQueryVariables,
} from 'graphql/requests/productLists/queries/ProductListQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { useEffect, useRef } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useClient } from 'urql';
import { useBroadcastChannel } from 'utils/useBroadcastChannel';

export const useRefetchComparedProducts = () => {
    const client = useClient();
    const comparisonUuid = usePersistStore((s) => s.productListUuids.COMPARISON);
    const comparisonUuidRef = useRef(comparisonUuid);

    useEffect(() => {
        comparisonUuidRef.current = comparisonUuid;
    }, [comparisonUuid]);

    useBroadcastChannel('refetchComparedProducts', async () => {
        if (comparisonUuidRef.current) {
            await client
                .query<TypeProductListQuery, TypeProductListQueryVariables>(
                    ProductListQueryDocument,
                    {
                        input: { type: TypeProductListTypeEnum.Comparison, uuid: comparisonUuidRef.current },
                    },
                    { requestPolicy: 'network-only' },
                )
                .toPromise();
        }
    });
};
