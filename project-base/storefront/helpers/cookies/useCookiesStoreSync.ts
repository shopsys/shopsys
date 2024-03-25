import { setCookie } from 'cookies-next';
import { OptionsType } from 'cookies-next/lib/types';
import { useEffect } from 'react';
import { CookiesStoreState } from 'store/createCookieStore';
import { useCookiesStore } from 'store/useCookiesStore';

const COOKIES_STORE_NAME = 'cookiesStore' as const;
const THIRTY_DAYS_IN_SECONDS = 60 * 60 * 24 * 30;

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
