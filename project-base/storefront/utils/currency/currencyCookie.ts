import { getCookie, setCookie } from 'cookies-next';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { getCookieName } from 'utils/cookies/cookieNaming';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { getIsHttps, getProtocol } from 'utils/requestProtocol';

const CURRENCY_COOKIE_BASE_NAME = 'currencyCode';
const ONE_YEAR_IN_SECONDS = 60 * 60 * 24 * 365;

export const getCurrencyCodeFromCookies = (
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext | NextPageContext,
): string | undefined => {
    const currencyCode = getCookie(getCookieName(CURRENCY_COOKIE_BASE_NAME, domainConfig.domainId), {
        req: context?.req,
        res: context?.res,
    });

    return typeof currencyCode === 'string' && currencyCode.length > 0 ? currencyCode : undefined;
};

export const setCurrencyCodeToCookies = (
    currencyCode: string,
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext | NextPageContext,
): void => {
    setCookie(getCookieName(CURRENCY_COOKIE_BASE_NAME, domainConfig.domainId), currencyCode, {
        req: context?.req,
        res: context?.res,
        maxAge: ONE_YEAR_IN_SECONDS,
        path: '/',
        secure: getIsHttps(getProtocol(context)),
    });
};
