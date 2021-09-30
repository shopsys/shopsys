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
    if (!('user' in cookies)) {
        cookies.user = JSON.stringify({
            cartUuid: undefined,
            transportUuid: undefined,
            paymentUuid: undefined,
            personalPickupUuid: undefined,
        });
    }

    const userDataCookie = JSON.parse(cookies.user);
    const updatedUserDataCookie = {
        ...userDataCookie,
        ...updatedData,
    };

    nookies.set(ssrContext, 'user', JSON.stringify(updatedUserDataCookie), { path: '/' });
    return updatedUserDataCookie;
};

export const getUserDataCookie = (ssrContext?: GetServerSidePropsContext): UserDataCookieType => {
    const cookies = nookies.get(ssrContext);
    if (!('user' in cookies)) {
        const newUserDataCookie = {
            cartUuid: undefined,
            transportUuid: undefined,
            paymentUuid: undefined,
            personalPickupUuid: undefined,
        };
        nookies.set(ssrContext, 'user', JSON.stringify(newUserDataCookie), { path: '/' });
        return newUserDataCookie;
    }

    return JSON.parse(cookies.user);
};
