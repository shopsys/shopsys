import { createClient } from './createClient';
import { logException } from './errors/logException';
import { DocumentNode } from 'graphql';
import {
    AdvertsQueryDocumentApi,
    ArticlePlacementTypeEnumApi,
    ArticlesQueryDocumentApi,
    CurrentCustomerUserQueryApi,
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
    isMaintenance: boolean;
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
            prefetchedQueries.push({
                query: ArticlesQueryDocumentApi,
                variables: {
                    placement: [
                        ArticlePlacementTypeEnumApi.Footer1Api,
                        ArticlePlacementTypeEnumApi.Footer2Api,
                        ArticlePlacementTypeEnumApi.Footer3Api,
                        ArticlePlacementTypeEnumApi.Footer4Api,
                    ],
                    first: 100,
                },
            });
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
                const customerQueryResult = currentClient.readQuery<CurrentCustomerUserQueryApi>(
                    CurrentCustomerUserQueryDocumentApi,
                );

                const isLogged =
                    customerQueryResult?.data?.currentCustomerUser !== undefined &&
                    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
                    customerQueryResult?.data?.currentCustomerUser !== null;

                if (!isLogged) {
                    return {
                        redirect: {
                            statusCode: 302,
                            destination: getInternationalizedStaticUrls(['/login'], domainConfig.url)[0],
                        },
                    };
                }
            }
            const isMaintenance = resolvedQueries.some((query) => query.error?.response.status === 503);
            if (isMaintenance) {
                context.res.statusCode = 503;
            }

            return {
                props: {
                    ...(await loadNamespaces({ locale: domainConfig.defaultLocale, pathname: trimmedUrl })),
                    urqlState: currentSsrCache.extractData(),
                    isMaintenance,
                },
            };
        }
        return { props: {} as ServerSidePropsType };
    } catch (e) {
        logException(e);
        throw e;
    }
}
