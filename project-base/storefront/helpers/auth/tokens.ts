import { GetServerSidePropsContext } from 'next';
import { destroyCookie, parseCookies, setCookie } from 'nookies';
import { OptionalTokenType } from 'urql/types';

export const removeTokensFromCookies = (context?: GetServerSidePropsContext): void => {
    destroyCookie(context, 'accessToken', { path: '/' });
    destroyCookie(context, 'refreshToken', { path: '/' });
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
    return 'refreshToken' in parseCookies(context);
};

export const getTokensFromCookies = (context?: GetServerSidePropsContext): OptionalTokenType => {
    const cookies = parseCookies(context);
    const accessToken = cookies.accessToken;
    const refreshToken = cookies.refreshToken;

    return { accessToken, refreshToken };
};
