import { TypeProductListTypeEnum } from 'graphql/types';
import { createContext, ReactNode, useCallback, useContext, useEffect, useRef, useState } from 'react';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useProductListMetadata } from 'utils/productLists/useProductListMetadata';
import { dispatchBroadcastChannel, useBroadcastChannel } from 'utils/useBroadcastChannel';
import { usePageVisibility } from 'utils/usePageVisibility';
import { useRefetchOnTabFocus } from 'utils/useRefetchOnTabFocus';
import { useCurrentCustomerUserQueryData } from './CurrentCustomerUserProvider';
import { useDomainConfig } from './DomainConfigProvider';

type ProductListMetadata = ReturnType<typeof useProductListMetadata>;
const ProductListsContext = createContext<{
    lists: Record<TypeProductListTypeEnum, ProductListMetadata>;
    visible: boolean;
    invalidateSession: () => void;
    notifyChange: (type: TypeProductListTypeEnum, uuid: string | null) => void;
} | null>(null);

export const ProductListsProvider = ({ children }: { children: ReactNode }) => {
    const { domainId } = useDomainConfig();
    const loggedIn = useIsUserLoggedIn();
    const customerUuid = useCurrentCustomerUserQueryData()?.currentCustomerUser?.uuid ?? '';
    const accountKey = `${domainId}:${loggedIn}:${customerUuid}`;
    const visible = usePageVisibility();
    const [sessionInvalidated, setSessionInvalidated] = useState(false);
    const session = useRef(new AbortController());
    const getSessionSignal = useCallback(() => session.current.signal, []);
    useEffect(() => {
        if (session.current.signal.aborted) session.current = new AbortController();
        return () => session.current.abort();
    }, []);
    const active = visible && !sessionInvalidated;
    const wishlist = useProductListMetadata(TypeProductListTypeEnum.Wishlist, accountKey, active, getSessionSignal);
    const comparison = useProductListMetadata(TypeProductListTypeEnum.Comparison, accountKey, active, getSessionSignal);
    const lists = { WISHLIST: wishlist, COMPARISON: comparison };
    useRefetchOnTabFocus(() => {
        wishlist.invalidate();
        comparison.invalidate();
    });
    useBroadcastChannel(
        'refetchProductList',
        (data: { productListType: TypeProductListTypeEnum; uuid?: string | null }) => {
            const list = lists[data.productListType];
            if (!list) return;
            const selection = list.getSelection();
            if (data.uuid === undefined || selection.uuid === null || selection.uuid === data.uuid) list.invalidate();
        },
    );

    return (
        <ProductListsContext.Provider
            value={{
                lists,
                visible: active,
                notifyChange: (type, uuid) =>
                    dispatchBroadcastChannel('refetchProductList', domainId, { productListType: type, uuid }),
                invalidateSession: () => {
                    session.current.abort();
                    setSessionInvalidated(true);
                },
            }}
        >
            {children}
        </ProductListsContext.Provider>
    );
};

export const useProductLists = () => {
    const value = useContext(ProductListsContext);
    if (!value) throw new Error('ProductListsProvider is missing.');
    return value;
};

export const useProductListState = (type: TypeProductListTypeEnum) => useProductLists().lists[type];
