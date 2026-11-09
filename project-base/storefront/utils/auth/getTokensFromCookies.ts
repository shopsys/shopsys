import { IncomingMessage, ServerResponse } from 'node:http';
import { getCookie } from 'cookies-next/client';
import {
    ACCESS_TOKEN_COOKIE_NAME,
    REFRESH_TOKEN_COOKIE_NAME,
    REFRESH_TOKEN_PRESENT_COOKIE_NAME,
} from 'utils/auth/authConstants';
import { getCookieName } from 'utils/cookies/cookieNaming';
import { getCookies } from 'utils/cookies/getCookies';
import { DomainConfigType } from 'utils/domain/domainConfig';

export type AuthServerContext = {
    req?: IncomingMessage;
    res?: ServerResponse;
};

const getNonEmptyCookieValue = (cookieName: string, context?: AuthServerContext): string | undefined => {
    const cookie = context?.req
        ? getCookies(context)[cookieName]
        : typeof window === 'undefined'
          ? undefined
          : getCookie(cookieName);

    return typeof cookie === 'string' && cookie.length > 0 ? cookie : undefined;
};

export const getAccessTokenFromCookies = (
    domainConfig: DomainConfigType,
    context?: AuthServerContext,
): string | undefined => {
    return getNonEmptyCookieValue(getCookieName(ACCESS_TOKEN_COOKIE_NAME, domainConfig.domainId), context);
};

export const getRefreshTokenFromCookies = (
    domainConfig: DomainConfigType,
    context: AuthServerContext,
): string | undefined => {
    return getNonEmptyCookieValue(getCookieName(REFRESH_TOKEN_COOKIE_NAME, domainConfig.domainId), context);
};

export const hasRefreshTokenInCookies = (domainConfig: DomainConfigType, context?: AuthServerContext): boolean => {
    return (
        getNonEmptyCookieValue(getCookieName(REFRESH_TOKEN_PRESENT_COOKIE_NAME, domainConfig.domainId), context) === '1'
    );
};
