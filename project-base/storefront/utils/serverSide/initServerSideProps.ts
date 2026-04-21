import { Variables } from '@urql/exchange-graphcache';
import { GetServerSidePropsResult } from 'next';
import { ssrExchange } from 'urql';
import { createClient } from 'urql/createClient';
import { buildServerSideProps } from './buildServerSideProps';
import { prefetchLayoutQueries } from './prefetchLayoutQueries';
import { InitServerSidePropsParameters, ServerSidePropsType } from './types';

export { buildServerSideProps } from './buildServerSideProps';
export { prefetchLayoutQueries } from './prefetchLayoutQueries';
export type { ServerSidePropsType } from './types';

export const initServerSideProps = async <VariablesType extends Variables>({
    domainConfig,
    context,
    redisClient,
    t,
    authenticationConfig = {
        authenticationRequired: false,
    },
    prefetchedQueries: additionalPrefetchQueries = [],
    client,
    ssrExchange: ssrExchangeOverride,
    additionalProps = {},
    currentCustomerUserPrefetchMode,
}: InitServerSidePropsParameters<VariablesType>): Promise<
    GetServerSidePropsResult<Omit<ServerSidePropsType, 'cookiesStore'>>
> => {
    const currentSsrCache = ssrExchangeOverride ?? ssrExchange({ isClient: false });
    const currentClient =
        client ??
        createClient({
            t,
            ssrExchange: currentSsrCache,
            domainConfig,
            redisClient,
            context,
        });

    const layoutResult = await prefetchLayoutQueries({
        client: currentClient,
        context,
        domainConfig,
        prefetchedQueries: additionalPrefetchQueries,
        currentCustomerUserPrefetchMode,
    });

    return buildServerSideProps({
        layoutResult,
        client: currentClient,
        ssrExchange: currentSsrCache,
        context,
        domainConfig,
        authenticationConfig,
        additionalProps,
    });
};
