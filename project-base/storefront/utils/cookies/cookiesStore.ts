import { getCookies, setCookie } from 'cookies-next';
import { GetServerSidePropsContext } from 'next';
import { useEffect } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
import { v4 as uuidV4 } from 'uuid';
import { createStore } from 'zustand/vanilla';

export type CookiesStoreState = {
    lastVisitedProductsCatnums: string[] | null;
    userIdentifier: string;
};

type CookiesStoreActions = {
    setCookiesStoreState: (value: Partial<CookiesStoreState>) => void;
};

export type CookiesStore = CookiesStoreState & CookiesStoreActions;

type CookiesType = {
    cookiesStore: string;
};

const COOKIES_STORE_NAME = 'cookiesStore' as const;
const THIRTY_DAYS_IN_SECONDS = 60 * 60 * 24 * 30;

const getDefaultInitState = (): CookiesStoreState => ({
    lastVisitedProductsCatnums: null,
    userIdentifier: uuidV4(),
});

export const getCookiesStoreState = (context?: GetServerSidePropsContext): CookiesStoreState => {
    const { cookiesStore } = getCookies(context) as CookiesType;

    return cookiesStore ? JSON.parse(decodeURIComponent(cookiesStore)) : getDefaultInitState();
};

export const useCookiesStoreSync = () => {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { setCookiesStoreState, ...storeValues } = useCookiesStore((state) => state);

    useEffect(() => {
        setCookie(COOKIES_STORE_NAME, storeValues, { maxAge: THIRTY_DAYS_IN_SECONDS });
    }, [storeValues]);
};

export const createCookiesStore = (cookieStoreFromServer: CookiesStoreState) =>
    createStore<CookiesStore>()((set) => ({
        ...cookieStoreFromServer,
        setCookiesStoreState: (value) => {
            set((state) => ({ ...state, ...value }));
        },
    }));
