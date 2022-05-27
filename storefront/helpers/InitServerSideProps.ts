import { createClient } from './createClient';
import { logException } from './errors/logException';
import { DocumentNode } from 'graphql';
import {
    AdvertsQueryDocumentApi,
    CurrentCustomerUserQueryDocumentApi,
    NavigationQueryDocumentApi,
    NotificationBarsDocumentApi,
    SettingsQueryDocumentApi,
} from 'graphql/generated';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import loadNamespaces from 'next-translate/loadNamespaces';
import { SSRData, SSRExchange } from 'next-urql';
import { AppStore } from 'redux/main';
import { Client, ssrExchange } from 'urql';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

export type ServerSidePropsType = {
    urqlState: SSRData;
};

export async function initServerSideProps(
    context: GetServerSidePropsContext,
    store: AppStore,
    authenticationRequired = false,
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
            // eslint-disable-next-line require-atomic-updates
            currentClient = await createClient(context, store, currentSsrCache);
        }

        if (currentClient !== null) {
            prefetchedQueries.push({ query: NotificationBarsDocumentApi });
            prefetchedQueries.push({ query: NavigationQueryDocumentApi });
            prefetchedQueries.push({ query: AdvertsQueryDocumentApi });
            prefetchedQueries.push({ query: CurrentCustomerUserQueryDocumentApi });
            prefetchedQueries.push({ query: SettingsQueryDocumentApi });

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

            if (authenticationRequired) {
                const customerResult = resolvedQueries.find((query) => query.data?.currentCustomerUser !== undefined);
                const isLogged = customerResult?.data.currentCustomerUser !== undefined;

                if (!isLogged) {
                    return {
                        redirect: {
                            statusCode: 302,
                            destination: getInternationalizedStaticUrls(['/login'], domainConfig.url)[0],
                        },
                    };
                }
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
        logException(e);
        throw e;
    }
}
