import { useProductListCountQuery } from 'graphql/requests/productLists/queries/ProductListCountQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { useCallback, useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useRefetchOnTabFocus } from 'utils/useRefetchOnTabFocus';

export const useProductListCount = (productListType: TypeProductListTypeEnum): number => {
    const isProductListHydrated = useSessionStore((s) => s.isProductListHydrated);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const productListUuid = usePersistStore((s) => s.productListUuids[productListType]) ?? null;
    const isUserLoggedIn = useIsUserLoggedIn();

    const isQueryPaused = !isProductListHydrated || (!productListUuid && !isUserLoggedIn);

    const [{ data }, reexecuteQuery] = useProductListCountQuery({
        variables: {
            input: {
                type: productListType,
                uuid: productListUuid,
            },
        },
        pause: isQueryPaused,
    });

    const refetchOnFocus = useCallback(() => {
        if (isQueryPaused) {
            return;
        }
        reexecuteQuery({ requestPolicy: 'network-only' });
    }, [reexecuteQuery, isQueryPaused]);
    useRefetchOnTabFocus(refetchOnFocus);

    useEffect(() => {
        updatePageLoadingState({ isProductListHydrated: true });
    }, [updatePageLoadingState]);

    return data?.productList?.itemsCount ?? 0;
};
