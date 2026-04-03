import { getCookie } from 'cookies-next';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { OptionalTokenType } from 'urql/types';
import { getCookieName } from 'utils/cookies/cookieNaming';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { getIsHttps, getProtocol } from 'utils/requestProtocol';

export const getTokensFromCookies = (
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext | NextPageContext,
): OptionalTokenType => {
    let accessToken = getCookie(getCookieName('accessToken', domainConfig.domainId), {
        req: context?.req,
        res: context?.res,
        secure: getIsHttps(getProtocol(context)),
    });
    let refreshToken = getCookie(getCookieName('refreshToken', domainConfig.domainId), {
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
