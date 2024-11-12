import { getAuthExchangeOptions } from './authExchange';
import { devtoolsExchange } from '@urql/devtools';
import { authExchange } from '@urql/exchange-auth';
import { Translate } from 'types/translation';
import { cacheExchange, ClientOptions, fetchExchange, SSRExchange } from 'urql';
import { dedupExchange } from 'urql/dedupExchange';
import { operationNameExchange } from 'urql/operationNameExchange';

export const getUrqlExchanges = async (ssrExchange: SSRExchange, t: Translate): Promise<ClientOptions['exchanges']> => [
    devtoolsExchange,
    dedupExchange,
    //cache,
    //getErrorExchange(t),
    ssrExchange,
    cacheExchange,
    authExchange(getAuthExchangeOptions()),
    operationNameExchange,
    fetchExchange,
];
