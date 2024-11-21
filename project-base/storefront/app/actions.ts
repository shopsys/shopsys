'use server';

import { setCookie } from 'cookies-next';
import { headers, cookies } from 'next/headers';
import { getIsHttps, getProtocolFromServer } from 'utils/requestProtocol';

export async function setTokensToCookiesServer(accessToken: string, refreshToken: string): Promise<void> {
    await setCookie('accessToken', accessToken, {
        cookies,
        path: '/',
        secure: getIsHttps(getProtocolFromServer(headers().get('host')!)),
    });
    await setCookie('refreshToken', refreshToken, {
        cookies,
        maxAge: 3600 * 24 * 14,
        path: '/',
        secure: getIsHttps(getProtocolFromServer(headers().get('host')!)),
    });
}
