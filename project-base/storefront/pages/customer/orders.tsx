import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { OrdersContent } from 'components/Pages/Customer/Orders/OrdersContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import {
    useOrdersQuery,
    TypeOrdersQueryVariables,
    OrdersQueryDocument,
} from 'graphql/requests/orders/queries/OrdersQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { getNumberFromUrlQuery } from 'utils/parsing/getNumberFromUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const OrdersPage: FC = () => {
    const { t } = useTranslation();
    const currentPage = useCurrentPageQuery();
    const { url } = useDomainConfig();
    const [{ data: ordersData, fetching: areOrdersFetching }] = useOrdersQuery({
        variables: { after: getEndCursor(currentPage), first: DEFAULT_PAGE_SIZE },
        requestPolicy: 'cache-and-network',
    });
    const mappedOrders = useMemo(
        () => mapConnectionEdges<TypeListedOrderFragment>(ordersData?.orders?.edges),
        [ordersData?.orders?.edges],
    );
    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CustomerLayout breadcrumbs={breadcrumbs} pageHeading={t('My orders')} title={t('My orders')}>
                <OrdersContent
                    areOrdersFetching={areOrdersFetching}
                    orders={mappedOrders}
                    totalCount={ordersData?.orders?.totalCount}
                />
            </CustomerLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);

    return initServerSideProps<TypeOrdersQueryVariables>({
        context,
        authenticationRequired: true,
        prefetchedQueries: [
            {
                query: OrdersQueryDocument,
                variables: {
                    after: getEndCursor(page),
                    first: DEFAULT_PAGE_SIZE,
                },
            },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default OrdersPage;
