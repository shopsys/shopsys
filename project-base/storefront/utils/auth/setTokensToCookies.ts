import { IncomingMessage, ServerResponse } from 'node:http';
import { setCookie } from 'cookies-next';
import {
    ACCESS_TOKEN_COOKIE_NAME,
    REFRESH_TOKEN_COOKIE_MAX_AGE,
    REFRESH_TOKEN_COOKIE_NAME,
    REFRESH_TOKEN_PRESENT_COOKIE_NAME,
} from 'utils/auth/authConstants';
import { getCookieName } from 'utils/cookies/cookieNaming';
import { DomainConfigType } from 'utils/domain/domainConfig';

type AuthServerContext = {
    req?: IncomingMessage;
    res?: ServerResponse;
};

export const setTokensToCookies = (
    accessToken: string,
    refreshToken: string,
    domainConfig: DomainConfigType,
    context: AuthServerContext,
): void => {
    setCookie(getCookieName(ACCESS_TOKEN_COOKIE_NAME, domainConfig.domainId), accessToken, {
        httpOnly: false,
        req: context?.req,
        res: context?.res,
        path: '/',
        sameSite: 'lax',
        secure: true,
    });
    setCookie(getCookieName(REFRESH_TOKEN_COOKIE_NAME, domainConfig.domainId), refreshToken, {
        httpOnly: true,
        req: context?.req,
        res: context?.res,
        maxAge: REFRESH_TOKEN_COOKIE_MAX_AGE,
        path: '/',
        sameSite: 'lax',
        secure: true,
    });
    setCookie(getCookieName(REFRESH_TOKEN_PRESENT_COOKIE_NAME, domainConfig.domainId), '1', {
        httpOnly: false,
        req: context?.req,
        res: context?.res,
        maxAge: REFRESH_TOKEN_COOKIE_MAX_AGE,
        path: '/',
        sameSite: 'lax',
        secure: true,
    });
};
