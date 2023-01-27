import getConfig from 'next/config';
import { parseCookies, setCookie } from 'nookies';

export const getCartExpireDate = (): Date => {
    const { publicRuntimeConfig } = getConfig();
    const expireDate = new Date();
    expireDate.setDate(expireDate.getDate() + Number(publicRuntimeConfig.reduxExpirationDays));

    return expireDate;
};

export const extendCartExpireDate = (): void => {
    const cookies = parseCookies();
    setCookie(undefined, 'user', cookies.user, { expires: getCartExpireDate() });
};
