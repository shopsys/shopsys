import { createContext, useState } from 'react';
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
    const [store] = useState(() => createCookiesStore(cookieStoreFromServer));

    return <CookiesStoreContext.Provider value={store}>{children}</CookiesStoreContext.Provider>;
};
