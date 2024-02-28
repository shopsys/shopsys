import { CookiesStoreContext } from 'components/providers/CookiesStoreProvider';
import { useContext } from 'react';
import { useStore } from 'zustand';
import { createStore } from 'zustand/vanilla';

export type CookiesStoreState = { lastVisitedProductsCatnums: string[] | undefined };

export type CookiesStoreActions = {
    setCookiesStoreState: (value: Partial<CookiesStoreState>) => void;
};

export type CookiesStore = CookiesStoreState & CookiesStoreActions;

export const defaultInitState: CookiesStoreState = {
    lastVisitedProductsCatnums: undefined,
};

export const createCookiesStore = (initState: CookiesStoreState = defaultInitState) =>
    createStore<CookiesStore>()((set) => ({
        ...initState,
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
