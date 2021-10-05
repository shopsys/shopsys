import { GetServerSidePropsContext } from 'next';
import nookies from 'nookies';

export type UserDataCookieType = {
    cartUuid?: string;
    transportUuid?: string;
    paymentUuid?: string;
    personalPickupUuid?: string;
};

export const updateUserDataCookie = (
    updatedData: UserDataCookieType,
    ssrContext?: GetServerSidePropsContext,
): UserDataCookieType | undefined => {
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
        cartUuid: undefined,
        transportUuid: undefined,
        paymentUuid: undefined,
        personalPickupUuid: undefined,
    };
};

const setUserDataCookie = (cookieContent: UserDataCookieType, ssrContext?: GetServerSidePropsContext) => {
    nookies.set(ssrContext, 'user', JSON.stringify(cookieContent), { path: '/' });
};
