'use server';

import { setCookie } from 'cookies-next';
import { cookies, headers } from 'next/headers';
import { getIsHttps, getProtocolFromServer } from 'utils/requestProtocol';

export const setTokensToCookiesServer = (accessToken: string, refreshToken: string): void => {
    setCookie('accessToken', accessToken, {
        cookies,
        path: '/',
        secure: getIsHttps(getProtocolFromServer(headers().get('host')!)),
    });
    setCookie('refreshToken', refreshToken, {
        cookies,
        maxAge: 3600 * 24 * 14,
        path: '/',
        secure: getIsHttps(getProtocolFromServer(headers().get('host')!)),
    });
};
