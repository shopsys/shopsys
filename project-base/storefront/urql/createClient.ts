import { getServerConfigProperty } from 'envConfig';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { Translate } from 'next-translate';
// biome-ignore lint/style/noRestrictedImports: This file owns the approved initUrqlClient bridge for the storefront.
import { initUrqlClient } from 'next-urql';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { Client, SSRExchange } from 'urql';
import { getUrqlExchanges } from 'urql/exchanges';
import { fetcher } from 'urql/fetcher';
import { AUTH_DOMAIN_ID_HEADER } from 'utils/auth/authConstants';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { getExplicitPathDomainLocaleOrDefault, getInternalGraphqlEndpoint } from 'utils/domain/domainUtils';

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
    redisClient?: RedisClientType<RedisModules, RedisFunctions, RedisScripts>;
    context?: GetServerSidePropsContext | NextPageContext;
}): Client => {
    const locale = context?.locale ?? getExplicitPathDomainLocaleOrDefault(domainConfig.url);
    const internalGraphqlEndpoint = getInternalGraphqlEndpoint(
        getServerConfigProperty('internalGraphqlEndpoint'),
        locale,
    );
    const publicGraphqlEndpoint = domainConfig.publicGraphqlEndpoint;
    const publicGraphqlEndpointObject = new URL(publicGraphqlEndpoint);

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
            fetch: fetcher(redisClient),
        },
        false,
    );
};
