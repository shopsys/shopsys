import { setCookie } from 'cookies-next';
import { GetServerSidePropsContext, NextPageContext } from 'next';

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
