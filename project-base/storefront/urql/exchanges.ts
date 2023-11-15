import { dedupExchange } from './dedupExchange';
import { getErrorExchange } from './errorExchange';
import { devtoolsExchange } from '@urql/devtools';
import { authExchange } from '@urql/exchange-auth';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { Translate } from 'next-translate';
import { ClientOptions, fetchExchange, SSRExchange } from 'urql';
import { getAuthExchangeOptions } from 'urql/authExchange';
import { cache } from 'urql/cache/cacheExchange';

export const getUrqlExchanges = (
    ssrExchange: SSRExchange,
    t?: Translate,
    context?: GetServerSidePropsContext | NextPageContext,
): ClientOptions['exchanges'] => [
    devtoolsExchange,
    dedupExchange,
    cache,
    getErrorExchange(t, context),
    ssrExchange,
    authExchange(getAuthExchangeOptions(context)),
    fetchExchange,
];
