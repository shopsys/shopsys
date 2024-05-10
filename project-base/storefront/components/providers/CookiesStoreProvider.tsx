import { createContext, useRef } from 'react';
import { CookiesStore, CookiesStoreState, createCookiesStore } from 'utils/cookies/cookiesStore';
import { type StoreApi } from 'zustand';

export const CookiesStoreContext = createContext<StoreApi<CookiesStore> | null>(null);

type CookiesStoreProviderProps = {
    cookieStoreStateFromServer: CookiesStoreState;
};

export const CookiesStoreProvider: FC<CookiesStoreProviderProps> = ({
    children,
    cookieStoreStateFromServer: cookieStoreFromServer,
}) => {
    const storeRef = useRef<StoreApi<CookiesStore>>(createCookiesStore(cookieStoreFromServer));

    return <CookiesStoreContext.Provider value={storeRef.current}>{children}</CookiesStoreContext.Provider>;
};
