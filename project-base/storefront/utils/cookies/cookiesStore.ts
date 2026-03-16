import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { getCookies, setCookie } from 'cookies-next';
import { getPublicConfigProperty } from 'envConfig';
import { GetServerSidePropsContext } from 'next';
import { useEffect } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
import { getCookieName } from 'utils/cookies/cookieNaming';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { getIsHttps } from 'utils/requestProtocol';
import { v4 as uuidV4 } from 'uuid';
import { createStore } from 'zustand/vanilla';

export type CookiesStoreState = {
    lastVisitedProductsCatnums: string[] | null;
    userIdentifier: string;
    isUserSnapEnabled: boolean;
};

type CookiesStoreActions = {
    setCookiesStoreState: (value: Partial<CookiesStoreState>) => void;
};

export type CookiesStore = CookiesStoreState & CookiesStoreActions;

const getCookiesStoreName = (domainConfig: DomainConfigType): string => {
    return getCookieName('cookiesStore', domainConfig.domainId);
};
const THIRTY_DAYS_IN_SECONDS = 60 * 60 * 24 * 30;

const userSnapEnabledDefaultValue = getPublicConfigProperty('userSnapEnabledDefaultValue');

const getDefaultInitState = (): CookiesStoreState => ({
    lastVisitedProductsCatnums: null,
    userIdentifier: uuidV4(),
    isUserSnapEnabled: userSnapEnabledDefaultValue,
});

export const getCookiesStoreState = (
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext,
): CookiesStoreState => {
    const cookiesStoreName = getCookiesStoreName(domainConfig);

    const cookies = getCookies(context) as Record<string, string>;

    const cookiesStore = cookies[cookiesStoreName];
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
    const domainConfig = useDomainConfig();

    useEffect(() => {
        const cookiesStoreName = getCookiesStoreName(domainConfig);

        setCookie(cookiesStoreName, storeValues, {
            maxAge: THIRTY_DAYS_IN_SECONDS,
            secure: getIsHttps(),
            path: '/',
        });
    }, [storeValues, domainConfig]);
};

export const createCookiesStore = (cookieStoreFromServer: CookiesStoreState) =>
    createStore<CookiesStore>()((set) => ({
        ...cookieStoreFromServer,
        setCookiesStoreState: (value) => {
            set((state) => ({ ...state, ...value }));
        },
    }));
