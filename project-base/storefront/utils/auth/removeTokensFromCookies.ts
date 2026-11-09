import { IncomingMessage, ServerResponse } from 'node:http';
import { deleteCookie as deleteClientCookie } from 'cookies-next/client';
import { deleteCookie as deleteServerCookie } from 'cookies-next/server';
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

const deleteAuthCookie = (
    cookieName: string,
    domainConfig: DomainConfigType,
    context: AuthServerContext,
): Promise<void> => {
    return deleteServerCookie(getCookieName(cookieName, domainConfig.domainId), {
        req: context?.req,
        res: context?.res,
        path: '/',
        sameSite: 'lax',
        secure: domainConfig.url.startsWith('https'),
    });
};

export const removeAccessTokenFromCookies = (domainConfig: DomainConfigType): void => {
    deleteClientCookie(getCookieName(ACCESS_TOKEN_COOKIE_NAME, domainConfig.domainId), {
        path: '/',
        sameSite: 'lax',
        secure: domainConfig.url.startsWith('https'),
    });
};

export const removeTokensFromCookies = async (
    domainConfig: DomainConfigType,
    context: AuthServerContext,
): Promise<void> => {
    await deleteAuthCookie(ACCESS_TOKEN_COOKIE_NAME, domainConfig, context);
    await deleteAuthCookie(REFRESH_TOKEN_COOKIE_NAME, domainConfig, context);
    await deleteAuthCookie(REFRESH_TOKEN_PRESENT_COOKIE_NAME, domainConfig, context);
};
