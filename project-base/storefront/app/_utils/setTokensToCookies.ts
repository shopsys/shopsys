import { setCookie } from 'cookies-next';
import { headers, cookies } from 'next/headers';
import { getIsHttps, getProtocolFromServer } from 'utils/requestProtocol';

export function setTokensToCookies(accessToken: string, refreshToken: string) {
    const protocol = getIsHttps(getProtocolFromServer(headers().get('host')!));

    setCookie('accessToken', accessToken, {
        cookies,
        path: '/',
        secure: protocol,
    });

    setCookie('refreshToken', refreshToken, {
        cookies,
        maxAge: 3600 * 24 * 14,
        path: '/',
        secure: protocol,
    });
}
