import { getCookie } from 'cookies-next';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { OptionalTokenType } from 'urql/types';
import { getProtocol, getIsHttps } from 'utils/requestProtocol';

export const getTokensFromCookies = (context?: GetServerSidePropsContext | NextPageContext): OptionalTokenType => {
    let accessToken = getCookie('accessToken', {
        req: context?.req,
        res: context?.res,
        secure: getIsHttps(getProtocol(context)),
    });
    let refreshToken = getCookie('refreshToken', {
        req: context?.req,
        res: context?.res,
        secure: getIsHttps(getProtocol(context)),
    });

    if (typeof accessToken !== 'string' || accessToken.length === 0) {
        accessToken = undefined;
    }

    if (typeof refreshToken !== 'string' || refreshToken.length === 0) {
        refreshToken = undefined;
    }

    return { accessToken, refreshToken };
};
