import { isIP } from 'net';
import { GetServerSidePropsResult } from 'next';
import loadNamespaces from 'next-translate/loadNamespaces';
import { getCurrentCustomerUserRoles } from 'utils/auth/getCurrentCustomerUserRoles';
import { getIsUserAuthorizedToViewPage } from 'utils/auth/getIsUserAuthorizedToViewPage';
import { getUnauthenticatedRedirectSSR } from 'utils/auth/getUnauthenticatedRedirectSSR';
import { isUserLoggedInSSR } from 'utils/auth/isUserLoggedInSSR';
import { isEnvironment } from 'utils/isEnvironment';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';
import { getServerSideInternationalizedStaticUrl } from 'utils/staticUrls/getServerSideInternationalizedStaticUrl';
import { applyStorefrontDevelopmentCspAppendices } from './cspHelpers';
import { BuildServerSidePropsParams, ServerSidePropsType } from './types';

export const buildServerSideProps = async ({
    layoutResult,
    client,
    ssrExchange: ssrCache,
    context,
    domainConfig,
    authenticationConfig = { authenticationRequired: false },
    additionalProps = {},
    pageQueryResults = [],
}: BuildServerSidePropsParams): Promise<GetServerSidePropsResult<Omit<ServerSidePropsType, 'cookiesStore'>>> => {
    const { resolvedQueries } = layoutResult;

    const allResults = [...pageQueryResults, ...resolvedQueries];
    const settingsResult = allResults.find((query) => query.data?.settings?.cspHeader !== undefined);
    let cspHeaderValue = settingsResult?.data?.settings?.cspHeader;

    if (cspHeaderValue) {
        if (isEnvironment('development')) {
            cspHeaderValue = applyStorefrontDevelopmentCspAppendices(cspHeaderValue);
        }

        context.res.setHeader('Content-Security-Policy', cspHeaderValue);
    }

    const slugResult = allResults.find((query) => !!query.data?.slug?.slug);
    const parsedSlug = slugResult?.data.slug.slug;

    const { trimmedUrlWithoutQueryParams, queryParams } = getServerSideInternationalizedStaticUrl(
        context,
        domainConfig.url,
    );

    if (parsedSlug && parsedSlug !== trimmedUrlWithoutQueryParams) {
        return {
            redirect: {
                statusCode: 301,
                destination: `${parsedSlug}${queryParams ?? ''}`,
            },
        };
    }

    if (authenticationConfig.authenticationRequired) {
        const isUserLoggedIn = isUserLoggedInSSR(client);

        if (!isUserLoggedIn) {
            return getUnauthenticatedRedirectSSR(
                getUrlWithoutGetParameters(context.resolvedUrl),
                domainConfig.url,
                context,
            );
        }
    }

    let isForbidden = false;
    const customerUserRoles = getCurrentCustomerUserRoles(client);

    if (authenticationConfig.authorizedRoles || authenticationConfig.authorizedAreas) {
        const isUserAuthorized = getIsUserAuthorizedToViewPage(
            customerUserRoles,
            domainConfig.type,
            authenticationConfig.authorizedRoles,
            authenticationConfig.authorizedAreas,
        );

        if (!isUserAuthorized) {
            context.res.statusCode = 403;
            isForbidden = true;
        }
    }

    const isMaintenance = allResults.some((query) => query.error?.response?.status === 503);
    if (isMaintenance) {
        context.res.statusCode = 503;
    }

    const ipAddress = getIpAddress(context);

    return {
        props: {
            ...(await loadNamespaces({
                locale: domainConfig.defaultLocale,
                pathname: trimmedUrlWithoutQueryParams,
                namespaces: ['common', 'accessibility'],
            })),
            domainConfig,
            // JSON.parse(JSON.stringify()) fix of https://github.com/vercel/next.js/issues/11993
            urqlState: JSON.parse(JSON.stringify(ssrCache.extractData())),
            isMaintenance,
            isForbidden,
            customerUserRoles,
            ...(ipAddress !== undefined && { ipAddress }),
            ...additionalProps,
        },
    };
};

export const getIpAddress = (context: BuildServerSidePropsParams['context']): string | undefined => {
    const xForwardedFor = context.req?.headers['x-forwarded-for'];
    const rawXForwardedFor = Array.isArray(xForwardedFor) ? xForwardedFor.join(',') : xForwardedFor;
    const forwardedIpAddresses = rawXForwardedFor
        ?.split(',')
        .map((ipAddress) => ipAddress.trim())
        .filter(Boolean);
    const forwardedIpAddress = forwardedIpAddresses?.[forwardedIpAddresses.length - 1];

    if (forwardedIpAddress !== undefined && isIP(forwardedIpAddress)) {
        return forwardedIpAddress;
    }

    const remoteAddress = context.req?.socket?.remoteAddress;

    return remoteAddress !== undefined && isIP(remoteAddress) ? remoteAddress : undefined;
};
