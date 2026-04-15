import { deleteCookie } from 'cookies-next';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { getCookieName } from 'utils/cookies/cookieNaming';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { getIsHttps, getProtocol } from 'utils/requestProtocol';

export const removeTokensFromCookies = (
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext | NextPageContext,
): void => {
    deleteCookie(getCookieName('accessToken', domainConfig.domainId), {
        req: context?.req,
        res: context?.res,
        path: '/',
        secure: getIsHttps(getProtocol(context)),
    });
    deleteCookie(getCookieName('refreshToken', domainConfig.domainId), {
        req: context?.req,
        res: context?.res,
        path: '/',
        secure: getIsHttps(getProtocol(context)),
    });
};
