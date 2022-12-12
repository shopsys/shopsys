import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrdersContent } from 'components/Pages/Customer/Orders/OrdersContent';
import { useOrders } from 'connectors/customer/Orders';
import { OrdersQueryDocumentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const OrdersPage: FC = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const currentDomainConfig = useShopsysSelector((state) => state.domain);
    const { query } = useRouter();
    const currentPage = parsePageNumberFromQuery(query[PAGE_QUERY_PARAMETER_NAME]);
    const ordersData = useOrders(currentDomainConfig, currentPage);
    const [customerUrl, customerOrdersUrl] = getInternationalizedStaticUrls(
        ['/customer', '/customer/orders'],
        domainUrl,
    );
    const breadcrumbs = useMemo(
        () => [
            { name: t('Customer'), slug: customerUrl },
            { name: t('My orders'), slug: customerOrdersUrl },
        ],
        [customerUrl, customerOrdersUrl, t],
    );
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('other', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('My orders')}>
                <OrdersContent
                    orders={ordersData?.orders}
                    totalCount={ordersData?.totalCount}
                    breadcrumbs={breadcrumbs}
                />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => {
            const page = parsePageNumberFromQuery(context.query[PAGE_QUERY_PARAMETER_NAME]);

            return initServerSideProps({
                context,
                store,
                authenticationRequired: true,
                prefetchedQueries: [
                    {
                        query: OrdersQueryDocumentApi,
                        variables: {
                            after: getNewPagination(page === 0 ? 1 : page).endCursor ?? null,
                            pageSize: DEFAULT_PAGE_SIZE,
                        },
                    },
                ],
                redisClient,
            });
        },
        store,
    ),
);

export default OrdersPage;
