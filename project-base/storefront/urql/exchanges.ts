import { devtoolsExchange } from '@urql/devtools';
import { authExchange } from '@urql/exchange-auth';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { Translate } from 'next-translate';
import { ClientOptions, fetchExchange, SSRExchange } from 'urql';
import { getAuthExchangeOptions } from 'urql/authExchange';
import { cache } from 'urql/cache/cacheExchange';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { isClient } from 'utils/isClient';
import { dedupExchange } from './dedupExchange';
import { getErrorExchange } from './errorExchange';
import { operationNameExchange } from './operationNameExchange';

export const getUrqlExchanges = (
    ssrExchange: SSRExchange,
    t: Translate,
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext | NextPageContext,
): ClientOptions['exchanges'] => [
    devtoolsExchange,
    dedupExchange,
    // Graphcache runs only on the client. On the server, parallel query execution causes
    // normalization collisions when multiple queries return the same entity type (e.g. Category),
    // resulting in empty data and false 404s. Server-side readQuery() uses the SSRExchange instead
    // — see prefetchLayoutQueries() for details.
    ...(isClient ? [cache] : []),
    getErrorExchange(t, domainConfig, context),
    ssrExchange,
    authExchange(getAuthExchangeOptions(domainConfig, context)),
    operationNameExchange,
    fetchExchange,
];
