import { CookiesStoreContext } from 'components/providers/CookiesStoreProvider';
import { useContext } from 'react';
import { v4 as uuidV4 } from 'uuid';
import { useStore } from 'zustand';
import { createStore } from 'zustand/vanilla';

export type CookiesStoreState = {
    lastVisitedProductsCatnums: string[] | null;
    userIdentifier: string;
};

export type CookiesStoreActions = {
    setCookiesStoreStateOnClient: (value: Partial<CookiesStoreState>) => void;
};

export type CookiesStoreOnClient = CookiesStoreState & CookiesStoreActions;
export type CookiesStoreOnServer = CookiesStoreState;

const getDefaultInitState = (): CookiesStoreState => ({
    lastVisitedProductsCatnums: null,
    userIdentifier: uuidV4(),
});

export const createCookiesStore = (cookieStoreFromServer: CookiesStoreState) =>
    createStore<CookiesStoreOnClient>()((set) => ({
        ...cookieStoreFromServer,
        setCookiesStoreStateOnClient: (value) => {
            set((state) => ({ ...state, ...value }));
        },
    }));

export const createCookiesStoreOnServer = (cookieStoreStateFromContext: string | null) => {
    let initState = getDefaultInitState();

    if (cookieStoreStateFromContext) {
        initState = JSON.parse(cookieStoreStateFromContext);
    }

    /**
     * This cannot contain functions or non-serializable properties
     * If you want to include such things, you have to manually
     * serialize it before returning from getServerSideProps
     */
    return createStore<CookiesStoreOnServer>()(() => ({
        ...initState,
    }));
};

export const useCookiesStore = <T>(selector: (store: CookiesStoreOnClient) => T): T => {
    const cookiesStoreContext = useContext(CookiesStoreContext);

    if (!cookiesStoreContext) {
        throw new Error(`useCookiesStore must be use within CookiesStoreProvider`);
    }

    return useStore(cookiesStoreContext, selector);
};
