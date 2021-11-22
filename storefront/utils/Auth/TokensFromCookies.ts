import { destroyCookie, parseCookies, setCookie } from 'nookies';
import { GetServerSidePropsContext } from 'next';

export const removeTokensFromCookies = (context?: GetServerSidePropsContext): void => {
    destroyCookie(context, 'accessToken');
    destroyCookie(context, 'refreshToken');
};

export const setTokensToCookie = (
    accessToken: string,
    refreshToken: string,
    context?: GetServerSidePropsContext,
): void => {
    setCookie(context, 'accessToken', accessToken, {
        // maxAge should be decreased to 300 (5min) after FWCC-581 is resolved
        maxAge: 3600 * 24 * 14,
    });
    setCookie(context, 'refreshToken', refreshToken, {
        maxAge: 3600 * 24 * 14,
    });
};

export const hasTokenInCookie = (context?: GetServerSidePropsContext): boolean => {
    const cookies = parseCookies(context);

    return cookies?.refreshToken !== undefined;
};
