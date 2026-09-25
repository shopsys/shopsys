import { getServerConfigProperty } from 'envConfig';
import { CurrentCustomerUserAuthQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserAuthQuery.generated';
import { CurrentCustomerUserQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { Translate } from 'next-translate';
// biome-ignore lint/style/noRestrictedImports: This file owns the approved initUrqlClient bridge for the storefront.
import { initUrqlClient } from 'next-urql';
import { Client, createRequest, SSRExchange } from 'urql';
import { getUrqlExchanges } from 'urql/exchanges';
import { fetcher } from 'urql/fetcher';
import { AUTH_DOMAIN_ID_HEADER } from 'utils/auth/authConstants';
import { getAccessTokenFromCookies, getRefreshTokenFromCookies } from 'utils/auth/getTokensFromCookies';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { getExplicitPathDomainLocaleOrDefault, getInternalGraphqlEndpoint } from 'utils/domain/domainUtils';
import { isClient } from 'utils/isClient';
import type { AppRedisClient } from 'utils/redis/redisClient';

export const DOMAIN_ID_HEADER = AUTH_DOMAIN_ID_HEADER;

export const createClient = ({
    t,
    ssrExchange,
    domainConfig,
    redisClient,
    context,
}: {
    t: Translate;
    ssrExchange: SSRExchange;
    domainConfig: DomainConfigType;
    redisClient?: AppRedisClient;
    context?: GetServerSidePropsContext | NextPageContext;
}): Client => {
    const locale = context?.locale ?? getExplicitPathDomainLocaleOrDefault(domainConfig.url);
    const internalGraphqlEndpoint = getInternalGraphqlEndpoint(
        getServerConfigProperty('internalGraphqlEndpoint'),
        locale,
    );
    const publicGraphqlEndpoint = domainConfig.publicGraphqlEndpoint;
    const publicGraphqlEndpointObject = new URL(publicGraphqlEndpoint);

    if (
        !isClient &&
        context?.req &&
        !getAccessTokenFromCookies(domainConfig, context) &&
        !getRefreshTokenFromCookies(domainConfig, context)
    ) {
        // Preserve explicit guest data for SSR authorization and hydration without a backend round trip.
        // A refresh-only session must still reach the auth exchange to restore its access token.
        const anonymousCustomer = { data: JSON.stringify({ currentCustomerUser: null }) };
        ssrExchange.restoreData({
            [createRequest(CurrentCustomerUserAuthQueryDocument, {}).key]: anonymousCustomer,
            [createRequest(CurrentCustomerUserQueryDocument, {}).key]: anonymousCustomer,
        });
    }

    return initUrqlClient(
        {
            url: internalGraphqlEndpoint ?? publicGraphqlEndpoint,
            exchanges: getUrqlExchanges(ssrExchange, t, domainConfig, context),
            preferGetMethod: false,
            fetchOptions: {
                headers: {
                    OriginalHost: publicGraphqlEndpointObject.host,
                    [DOMAIN_ID_HEADER]: domainConfig.domainId.toString(),
                    'X-Forwarded-Proto': publicGraphqlEndpointObject.protocol === 'https:' ? 'on' : 'off',
                },
            },
            fetch: fetcher(redisClient, context?.res),
        },
        false,
    );
};
