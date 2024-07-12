import { deleteCookie } from 'cookies-next';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { getProtocol, getIsHttps } from 'utils/requestProtocol';

export const removeTokensFromCookies = (context?: GetServerSidePropsContext | NextPageContext): void => {
    deleteCookie('accessToken', {
        req: context?.req,
        res: context?.res,
        path: '/',
        secure: getIsHttps(getProtocol(context)),
    });
    deleteCookie('refreshToken', {
        req: context?.req,
        res: context?.res,
        path: '/',
        secure: getIsHttps(getProtocol(context)),
    });
};
