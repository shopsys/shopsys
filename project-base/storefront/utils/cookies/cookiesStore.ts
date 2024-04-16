import { getCookies, setCookie } from 'cookies-next';
import { GetServerSidePropsContext } from 'next';
import { useEffect } from 'react';
import { CookiesStoreState, useCookiesStore } from 'store/useCookiesStore';
import { v4 as uuidV4 } from 'uuid';

const COOKIES_STORE_NAME = 'cookiesStore' as const;
const THIRTY_DAYS_IN_SECONDS = 60 * 60 * 24 * 30;

type CookiesType = {
    cookiesStore: string;
};

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
    const { setCookiesStoreState: setCookiesStoreStateOnClient, ...storeValues } = useCookiesStore((state) => state);

    useEffect(() => {
        setCookie(COOKIES_STORE_NAME, storeValues, { maxAge: THIRTY_DAYS_IN_SECONDS });
    }, [storeValues]);
};
