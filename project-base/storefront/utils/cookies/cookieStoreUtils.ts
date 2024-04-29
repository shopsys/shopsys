import { getCookies, setCookie } from 'cookies-next';
import { GetServerSidePropsContext } from 'next';
import { useEffect } from 'react';
import { CookiesStore, CookiesStoreState, useCookiesStore } from 'store/useCookiesStore';

const COOKIES_STORE_NAME = 'cookiesStore' as const;
const THIRTY_DAYS_IN_SECONDS = 60 * 60 * 24 * 30;

type CookiesType = {
    cookiesStore: string;
};

export const getCookiesStoreStateFromContext = (context?: GetServerSidePropsContext) => {
    const { cookiesStore } = getCookies(context) as CookiesType;

    return cookiesStore ? decodeURIComponent(cookiesStore) : null;
};

export const useCookiesStoreSync = () => {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { setCookiesStoreState, ...storeValues } = useCookiesStore((state) => state);

    useEffect(() => {
        setCookie(COOKIES_STORE_NAME, storeValues, { maxAge: THIRTY_DAYS_IN_SECONDS });
    }, [storeValues]);
};

export const serializeCookieStoreOnServer = (cookieStore: CookiesStore): CookiesStoreState => ({
    lastVisitedProductsCatnums: cookieStore.lastVisitedProductsCatnums,
    userIdentifier: cookieStore.userIdentifier,
});
