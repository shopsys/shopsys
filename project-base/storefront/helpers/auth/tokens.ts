import { getCookie, setCookie, deleteCookie } from 'cookies-next';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { OptionalTokenType } from 'urql/types';

export const removeTokensFromCookies = (context?: GetServerSidePropsContext | NextPageContext): void => {
    deleteCookie('accessToken', { req: context?.req, res: context?.res, path: '/' });
    deleteCookie('refreshToken', { req: context?.req, res: context?.res, path: '/' });
};

export const setTokensToCookies = (
    accessToken: string,
    refreshToken: string,
    context?: GetServerSidePropsContext | NextPageContext,
): void => {
    setCookie('accessToken', accessToken, { req: context?.req, res: context?.res, path: '/' });
    setCookie('refreshToken', refreshToken, {
        req: context?.req,
        res: context?.res,
        maxAge: 3600 * 24 * 14,
        path: '/',
    });
};

export const getTokensFromCookies = (context?: GetServerSidePropsContext | NextPageContext): OptionalTokenType => {
    let accessToken = getCookie('accessToken', { req: context?.req, res: context?.res });
    let refreshToken = getCookie('refreshToken', { req: context?.req, res: context?.res });

    if (typeof accessToken !== 'string') {
        accessToken = undefined;
    }

    if (typeof refreshToken !== 'string') {
        refreshToken = undefined;
    }

    return { accessToken, refreshToken };
};
