import { devtoolsExchange } from '@urql/devtools';
import { authExchange } from '@urql/exchange-auth';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { Translate } from 'next-translate';
import { ClientOptions, fetchExchange, SSRExchange } from 'urql';
import { getAuthExchangeOptions } from 'urql/authExchange';
import { cache } from 'urql/cache/cacheExchange';
import { DomainConfigType } from 'utils/domain/domainConfig';
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
    cache,
    getErrorExchange(t, domainConfig, context),
    ssrExchange,
    authExchange(getAuthExchangeOptions(domainConfig, context)),
    operationNameExchange,
    fetchExchange,
];
