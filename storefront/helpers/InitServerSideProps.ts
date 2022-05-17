import { createClient } from './createClient';
import { captureException } from '@sentry/nextjs';
import { DocumentNode } from 'graphql';
import {
    AdvertsQueryDocumentApi,
    CurrentCustomerUserQueryDocumentApi,
    NavigationQueryDocumentApi,
    NotificationBarsDocumentApi,
} from 'graphql/generated';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import loadNamespaces from 'next-translate/loadNamespaces';
import { SSRData, SSRExchange } from 'next-urql';
import { AppStore } from 'redux/main';
import { Client, ssrExchange } from 'urql';

export type ServerSidePropsType = {
    urqlState: SSRData;
};

export async function initServerSideProps(
    context: GetServerSidePropsContext,
    store: AppStore,
    prefetchedQueries: { query: string | DocumentNode; variables?: { [key: string]: unknown } }[] = [],
    client: Client | null = null,
    ssrCache: SSRExchange | null = null,
): Promise<GetServerSidePropsResult<ServerSidePropsType>> {
    try {
        const domainConfig = store.getState().domain;
        let currentClient = client;
        let currentSsrCache = ssrCache;

        if (currentSsrCache === null) {
            currentSsrCache = ssrExchange({ isClient: false });
        }

        if (currentClient === null) {
            currentClient = createClient(context, store, currentSsrCache);
        }

        if (currentClient !== null) {
            prefetchedQueries.push({ query: NotificationBarsDocumentApi });
            prefetchedQueries.push({ query: NavigationQueryDocumentApi });
            prefetchedQueries.push({ query: AdvertsQueryDocumentApi });
            prefetchedQueries.push({ query: CurrentCustomerUserQueryDocumentApi });

            const resolvedQueries = await Promise.all(
                prefetchedQueries.map((queryObject) =>
                    currentClient!.query(queryObject.query, queryObject.variables).toPromise(),
                ),
            );
            const slugResult = resolvedQueries.find((query) => query.data?.slug?.slug !== undefined);
            const parsedSlug = slugResult?.data.slug.slug;
            const trimmedUrl = context.resolvedUrl.split('?')[0];

            if (parsedSlug !== undefined && parsedSlug !== trimmedUrl) {
                return {
                    redirect: {
                        statusCode: 301,
                        destination: parsedSlug,
                    },
                };
            }

            return {
                props: {
                    ...(await loadNamespaces({ locale: domainConfig.defaultLocale, pathname: trimmedUrl })),
                    urqlState: currentSsrCache.extractData(),
                },
            };
        }
        return { props: {} as ServerSidePropsType };
    } catch (e) {
        captureException(e);
        throw e;
    }
}
