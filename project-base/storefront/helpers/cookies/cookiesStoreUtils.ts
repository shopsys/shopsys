import { getCookies, setCookie } from 'cookies-next';
import { OptionsType } from 'cookies-next/lib/types';
import { GetServerSidePropsContext } from 'next';
import { useEffect } from 'react';
import { CookiesStoreState, useCookiesStore } from 'store/useCookiesStore';

const COOKIES_STORE_NAME = 'cookiesStore' as const;
const THIRTY_DAYS_IN_SECONDS = 60 * 60 * 24 * 30;

type CookiesType = {
    cookiesStore: string;
};

export const getCookiesStore = (context?: GetServerSidePropsContext) => {
    const { cookiesStore } = getCookies(context) as CookiesType;

    return cookiesStore ? decodeURIComponent(cookiesStore) : null;
};

export const useCookiesStoreSync = () => {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { setCookiesStoreState: setCookieStoreValue, ...storeValues } = useCookiesStore((state) => state);

    useEffect(() => {
        setCookieStore(storeValues);
    }, [storeValues]);
};

const setCookieStore = (store: CookiesStoreState, options?: OptionsType) => {
    const isStoreWithValues = !!Object.values(store).filter((storeValue) => storeValue !== undefined).length;

    if (isStoreWithValues) {
        setCookie(COOKIES_STORE_NAME, store, { maxAge: THIRTY_DAYS_IN_SECONDS, ...options });
    }
};
