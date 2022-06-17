import { setCookie } from 'nookies';

const USER_CONSENT_COOKIE_AGE = 60 * 60 * 24 * 30; // 30 days in seconds

export const setUserConsentCookie = (cookieContent: Record<string, unknown>): void => {
    setCookie(null, 'userConsent', JSON.stringify(cookieContent), {
        maxAge: USER_CONSENT_COOKIE_AGE,
        path: '/',
        sameSite: true,
    });
};
