import { dedupExchange } from './dedupExchange';
import { devtoolsExchange } from '@urql/devtools';
import { authExchange } from '@urql/exchange-auth';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { SSRExchange } from 'next-urql';
import { ClientOptions, fetchExchange } from 'urql';
import { getAuthExchangeOptions } from 'urql/authExchange';
import { cache } from 'urql/cacheExchange';
import { getErrorExchange } from './errorExchange';
import { Translate } from 'next-translate';

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
