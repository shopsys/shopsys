import { useProductListCountQuery } from 'graphql/requests/productLists/queries/ProductListCountQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';

export const useProductListCount = (productListType: TypeProductListTypeEnum): number => {
    const isProductListHydrated = useSessionStore((s) => s.isProductListHydrated);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const productListUuid = usePersistStore((s) => s.productListUuids[productListType]) ?? null;
    const authLoading = usePersistStore((s) => s.authLoading);
    const isUserLoggedIn = useIsUserLoggedIn();

    const isQueryPaused = !isProductListHydrated || (!productListUuid && !isUserLoggedIn) || authLoading !== null;

    const [{ data }] = useProductListCountQuery({
        variables: {
            input: {
                type: productListType,
                uuid: productListUuid,
            },
        },
        pause: isQueryPaused,
    });

    useEffect(() => {
        updatePageLoadingState({ isProductListHydrated: true });
    }, [updatePageLoadingState]);

    return data?.productList?.itemsCount ?? 0;
};
