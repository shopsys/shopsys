import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrdersContent } from 'components/Pages/Customer/OrdersContent';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    BreadcrumbFragmentApi,
    ListedOrderFragmentApi,
    OrdersQueryDocumentApi,
    useOrdersQueryApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { getNumberFromUrlQuery } from 'helpers/parsing/urlParsing';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useQueryParams } from 'hooks/useQueryParams';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';

const OrdersPage: FC = () => {
    const { t } = useTranslation();
    const { currentPage } = useQueryParams();
    const { url } = useDomainConfig();
    const [{ data: ordersData, fetching }] = useOrdersQueryApi({
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
            <CommonLayout breadcrumbs={breadcrumbs} title={t('My orders')}>
                <OrdersContent isLoading={fetching} orders={mappedOrders} totalCount={ordersData?.orders?.totalCount} />
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);

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
        domainConfig,
        t,
    });
});

export default OrdersPage;
