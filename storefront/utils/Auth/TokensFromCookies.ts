import { destroyCookie, parseCookies, setCookie } from 'nookies';
import { GetServerSidePropsContext } from 'next';
import { OptionalTokenType } from 'urql/types';

export const removeTokensFromCookies = (context?: GetServerSidePropsContext): void => {
    destroyCookie(context, 'accessToken');
    destroyCookie(context, 'refreshToken');
};

export const setTokensToCookie = (
    accessToken: string,
    refreshToken: string,
    context?: GetServerSidePropsContext,
): void => {
    setCookie(context, 'accessToken', accessToken, { path: '/' });
    setCookie(context, 'refreshToken', refreshToken, {
        maxAge: 3600 * 24 * 14,
        path: '/',
    });
};

export const hasTokenInCookie = (context?: GetServerSidePropsContext): boolean => {
    const cookies = parseCookies(context);

    return cookies?.refreshToken !== undefined;
};

export const getTokensFromCookies = (context?: GetServerSidePropsContext): OptionalTokenType => {
    const cookies = parseCookies(context);
    const accessToken = cookies.accessToken ?? undefined;
    const refreshToken = cookies.refreshToken ?? undefined;

    return { accessToken, refreshToken };
};
