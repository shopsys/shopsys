import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrdersContent } from 'components/Pages/Customer/OrdersContent';
import {
    BreadcrumbFragmentApi,
    ListedOrderFragmentApi,
    OrdersQueryDocumentApi,
    useOrdersQueryApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';

import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useQueryParams } from 'hooks/useQueryParams';
import { useMemo } from 'react';
import { GtmPageType } from 'types/gtm/enums';

const OrdersPage: FC = () => {
    const t = useTypedTranslationFunction();
    const { currentPage } = useQueryParams();
    const { url } = useDomainConfig();
    const [{ data: ordersData }] = useOrdersQueryApi({
        variables: { after: getEndCursor(currentPage), first: DEFAULT_PAGE_SIZE },
        requestPolicy: 'cache-and-network',
    });
    const mappedOrders = useMemo(
        () => mapConnectionEdges<ListedOrderFragmentApi>(ordersData?.orders?.edges),
        [ordersData?.orders?.edges],
    );
    const [customerUrl, customerOrdersUrl] = getInternationalizedStaticUrls(['/customer', '/customer/orders'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('Customer'), slug: customerUrl },
        { __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('My orders')}>
                <OrdersContent
                    orders={mappedOrders}
                    totalCount={ordersData?.orders?.totalCount}
                    breadcrumbs={breadcrumbs}
                />
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient) => async (context) => {
    const page = parsePageNumberFromQuery(context.query[PAGE_QUERY_PARAMETER_NAME]);

    return initServerSideProps({
        context,
        authenticationRequired: true,
        prefetchedQueries: [
            {
                query: OrdersQueryDocumentApi,
                variables: {
                    after: getEndCursor(page),
                    pageSize: DEFAULT_PAGE_SIZE,
                },
            },
        ],
        redisClient,
    });
});

export default OrdersPage;
