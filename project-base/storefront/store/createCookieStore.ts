import { createStore } from 'zustand/vanilla';

export type CookiesStoreState = { lastVisitedProductsCatnums: string[] | undefined };

export type CookiesStoreActions = {
    setCookiesStoreState: (value: Partial<CookiesStoreState>) => void;
};

export type CookiesStore = CookiesStoreState & CookiesStoreActions;

const defaultInitState: CookiesStoreState = {
    lastVisitedProductsCatnums: undefined,
};

export const createCookiesStore = (initState: CookiesStoreState = defaultInitState) =>
    createStore<CookiesStore>()((set) => ({
        ...initState,
        setCookiesStoreState: (value) => {
            set((state) => ({ ...state, ...value }));
        },
    }));
