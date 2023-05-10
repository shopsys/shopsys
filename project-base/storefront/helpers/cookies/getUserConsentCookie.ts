import { parseCookies } from 'nookies';
import { UserConsentFormType } from 'types/form';

export const getUserConsentCookie = (): UserConsentFormType | null => {
    const userConsentCookieString: string | undefined = parseCookies(undefined).userConsent;

    return userConsentCookieString ? JSON.parse(userConsentCookieString) : null;
};
