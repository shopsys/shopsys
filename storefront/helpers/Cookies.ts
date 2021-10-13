import { CartInput } from 'connectors/cart/types';
import { GetServerSidePropsContext } from 'next';
import nookies from 'nookies';
import { PriceApiType } from 'connectors/transports/types';

export type UserDataCookieType = {
    cartUuid: string | null;
    transport: {
        uuid: string;
        price: PriceApiType;
        personalPickupStoreUuid: string | null;
    } | null;
    payment: {
        uuid: string;
        price: PriceApiType;
    } | null;
    promoCode: string | null;
};

export const updateUserDataCookie = (
    updatedData: UserDataCookieType,
    ssrContext?: GetServerSidePropsContext,
): UserDataCookieType => {
    const cookies = nookies.get(ssrContext);
    const userDataCookie = !('user' in cookies) ? initUserDataCookie() : JSON.parse(cookies.user);
    const updatedUserDataCookie = {
        ...userDataCookie,
        ...updatedData,
    };

    setUserDataCookie(updatedUserDataCookie, ssrContext);
    return updatedUserDataCookie;
};

export const getUserDataCookie = (ssrContext?: GetServerSidePropsContext): UserDataCookieType => {
    const cookies = nookies.get(ssrContext);
    if (!('user' in cookies)) {
        const newUserDataCookie = initUserDataCookie();
        setUserDataCookie(newUserDataCookie, ssrContext);
        return newUserDataCookie;
    }

    return JSON.parse(cookies.user);
};

export const initUserDataCookie = (): UserDataCookieType => {
    return {
        cartUuid: null,
        transport: null,
        payment: null,
        promoCode: null,
    };
};

const setUserDataCookie = (cookieContent: UserDataCookieType, ssrContext?: GetServerSidePropsContext) => {
    nookies.set(ssrContext, 'user', JSON.stringify(cookieContent), { path: '/' });
};

export const getCartInputDataFromCookie = (ssrContext?: GetServerSidePropsContext): CartInput => {
    const userData = getUserDataCookie(ssrContext);
    return {
        cartUuid: userData.cartUuid,
        transport: userData.transport,
        payment: userData.payment,
        promoCode: userData.promoCode,
    };
};
