import { getCookies, setCookie } from 'cookies-next';
import { GetServerSidePropsContext } from 'next';
import { useEffect } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
import { getProtocol, getIsHttps } from 'utils/requestProtocol';
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
    const { cookiesStore } = getCookies({ ...context, secure: getIsHttps(getProtocol(context)) }) as CookiesType;
    const newState = getDefaultInitState();

    if (!cookiesStore) {
        return newState;
    }

    return removeIncorrectCookiesStoreProperties(
        Object.keys(newState),
        addMissingCookiesStoreProperties(newState, JSON.parse(decodeURIComponent(cookiesStore))),
    );
};

const addMissingCookiesStoreProperties = (
    newState: CookiesStoreState,
    cookiesStoreState: Partial<CookiesStoreState>,
) => {
    return { ...newState, ...cookiesStoreState };
};

const removeIncorrectCookiesStoreProperties = (
    allowedKeys: string[],
    cookiesStoreState: CookiesStoreState & Record<string, unknown>,
) => {
    const cookiesStoreStateWithoutIncorrectProperties = { ...cookiesStoreState };

    for (const key in cookiesStoreStateWithoutIncorrectProperties) {
        if (!allowedKeys.includes(key)) {
            delete cookiesStoreStateWithoutIncorrectProperties[key];
        }
    }

    return cookiesStoreStateWithoutIncorrectProperties;
};

export const useCookiesStoreSync = () => {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { setCookiesStoreState, ...storeValues } = useCookiesStore((state) => state);

    useEffect(() => {
        setCookie(COOKIES_STORE_NAME, storeValues, { maxAge: THIRTY_DAYS_IN_SECONDS, secure: getIsHttps() });
    }, [storeValues]);
};

export const createCookiesStore = (cookieStoreFromServer: CookiesStoreState) =>
    createStore<CookiesStore>()((set) => ({
        ...cookieStoreFromServer,
        setCookiesStoreState: (value) => {
            set((state) => ({ ...state, ...value }));
        },
    }));
