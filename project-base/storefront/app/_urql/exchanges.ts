import { getAuthExchangeOptions } from './authExchange';
import { cache } from './cache/cacheExchange';
import { devtoolsExchange } from '@urql/devtools';
import { authExchange } from '@urql/exchange-auth';
import { ClientOptions, fetchExchange, ssrExchange } from 'urql';
import { dedupExchange } from 'urql/dedupExchange';
import { operationNameExchange } from 'urql/operationNameExchange';

export const getUrqlExchanges = (): ClientOptions['exchanges'] => [
    devtoolsExchange,
    dedupExchange,
    cache,
    ssrExchange({ isClient: false }),
    authExchange(getAuthExchangeOptions()),
    operationNameExchange,
    fetchExchange,
];
