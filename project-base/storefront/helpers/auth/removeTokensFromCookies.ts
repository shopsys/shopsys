import { deleteCookie } from 'cookies-next';
import { GetServerSidePropsContext, NextPageContext } from 'next';

export const removeTokensFromCookies = (context?: GetServerSidePropsContext | NextPageContext): void => {
    deleteCookie('accessToken', { req: context?.req, res: context?.res, path: '/' });
    deleteCookie('refreshToken', { req: context?.req, res: context?.res, path: '/' });
};
