import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { SearchListIcon } from 'components/Basic/Icon/SearchListIcon';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { OrdersContent } from 'components/Pages/Customer/Orders/OrdersContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import {
    OrdersQueryDocument,
    TypeOrdersQueryVariables,
    useOrdersQuery,
} from 'graphql/requests/orders/queries/OrdersQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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
        variables: { after: getEndCursor(currentPage, 0, DEFAULT_ORDERS_SIZE), first: DEFAULT_ORDERS_SIZE },
        requestPolicy: 'cache-and-network',
    });
    const mappedOrders = mapConnectionEdges<TypeListedOrderFragment>(ordersData?.orders?.edges);
    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl },
    ];
    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <>
            <MetaRobots content="noindex" />

            <CustomerLayout breadcrumbs={breadcrumbs} title={t('My orders')}>
                <PageHero
                    icon={SearchListIcon}
                    title={t('My orders')}
                    description={t(
                        'View and manage your past orders, track order status, and monitor your shopping history.',
                    )}
                />

                <OrdersContent
                    areOrdersFetching={areOrdersFetching}
                    hasNextPage={ordersData?.orders?.pageInfo.hasNextPage}
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
        currentCustomerUserPrefetchMode: 'full',
        authenticationConfig: {
            authenticationRequired: true,
            authorizedRoles: [
                TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation,
                TypeCustomerUserRoleEnum.RoleApiCompanyOrdersView,
            ],
        },
        prefetchedQueries: [
            {
                query: OrdersQueryDocument,
                variables: {
                    after: getEndCursor(page, 0, DEFAULT_ORDERS_SIZE),
                    first: DEFAULT_ORDERS_SIZE,
                },
            },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default OrdersPage;
