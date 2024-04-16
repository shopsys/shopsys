import { createContext, useRef } from 'react';
import { CookiesStoreOnClient, CookiesStoreState, createCookiesStore } from 'store/useCookiesStore';
import { type StoreApi } from 'zustand';

export const CookiesStoreContext = createContext<StoreApi<CookiesStoreOnClient> | null>(null);

type CookiesStoreProviderProps = {
    cookieStoreStateFromServer: CookiesStoreState;
};

export const CookiesStoreProvider: FC<CookiesStoreProviderProps> = ({
    children,
    cookieStoreStateFromServer: cookieStoreFromServer,
}) => {
    const storeRef = useRef<StoreApi<CookiesStoreOnClient>>(createCookiesStore(cookieStoreFromServer));

    return <CookiesStoreContext.Provider value={storeRef.current}>{children}</CookiesStoreContext.Provider>;
};
