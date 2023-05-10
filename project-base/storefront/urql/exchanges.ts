import { dedupExchange } from './dedupExchange';
import { devtoolsExchange } from '@urql/devtools';
import { authExchange } from '@urql/exchange-auth';
import { removeTokensFromCookies } from 'helpers/auth/tokens';
import { GetServerSidePropsContext } from 'next';
import { SSRExchange } from 'next-urql';
import { ClientOptions, CombinedError, errorExchange, fetchExchange } from 'urql';
import { getAuthExchangeOptions } from 'urql/authExchange';
import { cache } from 'urql/cacheExchange';

export const getUrqlExchanges = (
    ssrExchange: SSRExchange,
    context?: GetServerSidePropsContext,
): ClientOptions['exchanges'] => [
    devtoolsExchange,
    dedupExchange,
    cache,
    ssrExchange,
    errorExchange({
        onError: (error: CombinedError) => {
            const isAuthError = error.response?.status === 401;

            if (isAuthError) {
                removeTokensFromCookies(context);
            }
        },
    }),
    authExchange(getAuthExchangeOptions(context)),
    fetchExchange,
];
