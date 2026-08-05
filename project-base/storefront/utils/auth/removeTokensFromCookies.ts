import { IncomingMessage, ServerResponse } from 'node:http';
import { deleteCookie } from 'cookies-next';
import {
    ACCESS_TOKEN_COOKIE_NAME,
    REFRESH_TOKEN_COOKIE_NAME,
    REFRESH_TOKEN_PRESENT_COOKIE_NAME,
} from 'utils/auth/authConstants';
import { getCookieName } from 'utils/cookies/cookieNaming';
import { DomainConfigType } from 'utils/domain/domainConfig';

type AuthServerContext = {
    req?: IncomingMessage;
    res?: ServerResponse;
};

const deleteAuthCookie = (cookieName: string, domainConfig: DomainConfigType, context?: AuthServerContext): void => {
    deleteCookie(getCookieName(cookieName, domainConfig.domainId), {
        req: context?.req,
        res: context?.res,
        path: '/',
        sameSite: 'lax',
        secure: domainConfig.url.startsWith('https'),
    });
};

export const removeAccessTokenFromCookies = (domainConfig: DomainConfigType, context?: AuthServerContext): void => {
    deleteAuthCookie(ACCESS_TOKEN_COOKIE_NAME, domainConfig, context);
};

export const removeTokensFromCookies = (domainConfig: DomainConfigType, context: AuthServerContext): void => {
    deleteAuthCookie(ACCESS_TOKEN_COOKIE_NAME, domainConfig, context);
    deleteAuthCookie(REFRESH_TOKEN_COOKIE_NAME, domainConfig, context);
    deleteAuthCookie(REFRESH_TOKEN_PRESENT_COOKIE_NAME, domainConfig, context);
};
