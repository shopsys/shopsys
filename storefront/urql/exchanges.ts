import { ClientOptions, dedupExchange, fetchExchange } from '@urql/core';
import { CombinedError, errorExchange } from 'urql';
import { authExchange } from '@urql/exchange-auth';
import cache from 'urql/cacheExchange';
import getAuthExchangeOptions from 'urql/authExchange';
import { GetServerSidePropsContext } from 'next';
import { removeTokensFromCookies } from 'utils/Auth/TokensFromCookies';
import { SSRExchange } from 'next-urql';

export const getUrqlExchanges = (
    ssrExchange: SSRExchange,
    context?: GetServerSidePropsContext,
): ClientOptions['exchanges'] => [
    dedupExchange,
    cache,
    ssrExchange,
    errorExchange({
        onError: (error: CombinedError) => {
            const isAuthError = error?.response?.status === 401;

            if (isAuthError) {
                removeTokensFromCookies(context);
            }
        },
    }),
    authExchange(getAuthExchangeOptions(context)),
    fetchExchange,
];
