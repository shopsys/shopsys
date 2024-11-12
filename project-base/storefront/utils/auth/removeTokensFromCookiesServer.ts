'use server';

import { deleteCookie } from 'cookies-next';
import { cookies, headers } from 'next/headers';
import { getIsHttps, getProtocolFromServer } from 'utils/requestProtocol';

export const removeTokensFromCookiesServer = (): void => {
    deleteCookie('accessToken', {
        cookies,
        path: '/',
        secure: getIsHttps(getProtocolFromServer(headers().get('host')!)),
    });
    deleteCookie('refreshToken', {
        cookies,
        path: '/',
        secure: getIsHttps(getProtocolFromServer(headers().get('host')!)),
    });
};
