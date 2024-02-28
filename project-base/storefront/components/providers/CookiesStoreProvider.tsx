import { createContext, useRef } from 'react';
import { CookiesStore, createCookiesStore } from 'store/useCookiesStore';
import { type StoreApi } from 'zustand';

export const CookiesStoreContext = createContext<StoreApi<CookiesStore> | null>(null);

export const CookiesStoreProvider: FC = ({ children }) => {
    const storeRef = useRef<StoreApi<CookiesStore>>(createCookiesStore());

    return <CookiesStoreContext.Provider value={storeRef.current}>{children}</CookiesStoreContext.Provider>;
};
