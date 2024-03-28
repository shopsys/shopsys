import { getCookie } from 'cookies-next';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { OptionalTokenType } from 'urql/types';

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
