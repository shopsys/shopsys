import { cookies } from 'next/headers';
import { v4 as uuidV4 } from 'uuid';
import { createStore } from 'zustand';

const userSnapEnabledDefaultValue = process.env.NEXT_PUBLIC_USERSNAP_STOREFRONT_ENABLED_BY_DEFAULT === '1';

export type CookiesStoreState = {
    lastVisitedProductsCatnums: string[] | null;
    userIdentifier: string;
    isUserSnapEnabled: boolean;
};

type CookiesStoreActions = {
    setCookiesStoreState: (value: Partial<CookiesStoreState>) => void;
};

const getDefaultInitState = (): CookiesStoreState => ({
    lastVisitedProductsCatnums: null,
    userIdentifier: uuidV4(),
    isUserSnapEnabled: userSnapEnabledDefaultValue,
});

export type CookiesStore = CookiesStoreState & CookiesStoreActions;

export function getCookieStoreStateFromServer(): CookiesStoreState {
    const cookiesStore = cookies().get('cookiesStore')?.value;
    const newState = getDefaultInitState();

    if (!cookiesStore) {
        return newState;
    }

    return removeIncorrectCookiesStoreProperties(
        Object.keys(newState),
        addMissingCookiesStoreProperties(newState, JSON.parse(decodeURIComponent(cookiesStore))),
    );
}

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

export const createCookiesStore = (cookieStoreFromServer: CookiesStoreState) =>
    createStore<CookiesStore>()((set) => ({
        ...cookieStoreFromServer,
        setCookiesStoreState: (value) => {
            set((state) => ({ ...state, ...value }));
        },
    }));
