import { setCookie } from 'nookies';
import { UserConsentFormType } from 'types/form';

const USER_CONSENT_COOKIE_AGE_IF_ALL_ACCEPTED = 60 * 60 * 24 * 365; // 365 days in seconds
const USER_CONSENT_COOKIE_AGE_IF_NOT_ALL_ACCEPTED = 60 * 60 * 24 * 30; // 30 days in seconds

export const setUserConsentCookie = (cookieContent: UserConsentFormType): void => {
    let cookieMaxAge = USER_CONSENT_COOKIE_AGE_IF_NOT_ALL_ACCEPTED;
    if (Object.values(cookieContent).every((value) => value)) {
        cookieMaxAge = USER_CONSENT_COOKIE_AGE_IF_ALL_ACCEPTED;
    }

    setCookie(null, 'userConsent', JSON.stringify({ ...cookieContent, createdAt: Date.now() }), {
        maxAge: cookieMaxAge,
        path: '/',
        sameSite: true,
    });
};
