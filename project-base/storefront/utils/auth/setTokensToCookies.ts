import { setCookie } from 'cookies-next';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { getCookieName } from 'utils/cookies/cookieNaming';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { getIsHttps, getProtocol } from 'utils/requestProtocol';

export const setTokensToCookies = (
    accessToken: string,
    refreshToken: string,
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext | NextPageContext,
): void => {
    setCookie(getCookieName('accessToken', domainConfig.domainId), accessToken, {
        req: context?.req,
        res: context?.res,
        path: '/',
        secure: getIsHttps(getProtocol(context)),
    });
    setCookie(getCookieName('refreshToken', domainConfig.domainId), refreshToken, {
        req: context?.req,
        res: context?.res,
        maxAge: 3600 * 24 * 14,
        path: '/',
        secure: getIsHttps(getProtocol(context)),
    });
};
