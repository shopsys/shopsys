import { setCookie } from 'cookies-next';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { getProtocol, getIsHttps } from 'utils/requestProtocol';

export const setTokensToCookies = (
    accessToken: string,
    refreshToken: string,
    context?: GetServerSidePropsContext | NextPageContext,
): void => {
    setCookie('accessToken', accessToken, {
        req: context?.req,
        res: context?.res,
        path: '/',
        secure: getIsHttps(getProtocol(context)),
    });
    setCookie('refreshToken', refreshToken, {
        req: context?.req,
        res: context?.res,
        maxAge: 3600 * 24 * 14,
        path: '/',
        secure: getIsHttps(getProtocol(context)),
    });
};
