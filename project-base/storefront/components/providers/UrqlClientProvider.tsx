'use client';

import { useDomainConfig } from './DomainConfigProvider';
import { devtoolsExchange } from '@urql/devtools';
import { authExchange } from '@urql/exchange-auth';
import { useMemo } from 'react';
// eslint-disable-next-line no-restricted-imports
import { Provider, SSRData, SSRExchange, createClient, ssrExchange as createSsrExchange, fetchExchange } from 'urql';
import { getAuthExchangeOptions } from 'urql/authExchange';
import { cache } from 'urql/cache/cacheExchange';
import { dedupExchange } from 'urql/dedupExchange';
import { fetcher } from 'urql/fetcher';
import { operationNameExchange } from 'urql/operationNameExchange';
import { isClient } from 'utils/isClient';

let ssrExchange: SSRExchange | null = null;
export const UrqlClientProvider: FC<{ urqlState: SSRData }> = ({ children, urqlState }) => {
    const { publicGraphqlEndpoint } = useDomainConfig();

    const client = useMemo(() => {
        if (!ssrExchange || typeof window === 'undefined') {
            ssrExchange = createSsrExchange({
                initialState: urqlState,
                isClient: isClient,
            });
        } else {
            ssrExchange.restoreData(urqlState);
        }

        return createClient({
            url: publicGraphqlEndpoint,
            exchanges: [
                devtoolsExchange,
                dedupExchange,
                cache,
                //getErrorExchange(t, context),
                ssrExchange,
                authExchange(getAuthExchangeOptions()),
                operationNameExchange,
                fetchExchange,
            ],
            fetch: fetcher(undefined),
        });
    }, [urqlState]);

    return <Provider value={client}>{children}</Provider>;
};
