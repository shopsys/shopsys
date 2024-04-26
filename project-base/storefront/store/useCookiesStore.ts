import { CookiesStoreContext } from 'components/providers/CookiesStoreProvider';
import { useContext } from 'react';
import { useStore } from 'zustand';
import { createStore } from 'zustand/vanilla';

export type CookiesStoreState = {
    lastVisitedProductsCatnums: string[] | null;
    userIdentifier: string;
};

type CookiesStoreActions = {
    setCookiesStoreState: (value: Partial<CookiesStoreState>) => void;
};

export type CookiesStore = CookiesStoreState & CookiesStoreActions;

export const createCookiesStore = (cookieStoreFromServer: CookiesStoreState) =>
    createStore<CookiesStore>()((set) => ({
        ...cookieStoreFromServer,
        setCookiesStoreState: (value) => {
            set((state) => ({ ...state, ...value }));
        },
    }));

export const useCookiesStore = <T>(selector: (store: CookiesStore) => T): T => {
    const cookiesStoreContext = useContext(CookiesStoreContext);

    if (!cookiesStoreContext) {
        throw new Error(`useCookiesStore must be use within CookiesStoreProvider`);
    }

    return useStore(cookiesStoreContext, selector);
};
