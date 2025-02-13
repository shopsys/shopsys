import { setCookie } from 'cookies-next';
import { cookies } from 'next/headers';

export function setTokensToCookies(accessToken: string, refreshToken: string) {
    setCookie('accessToken', accessToken, {
        cookies,
        path: '/',
    });

    setCookie('refreshToken', refreshToken, {
        cookies,
        maxAge: 3600 * 24 * 14,
        path: '/',
    });
}
