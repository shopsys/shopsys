import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { PageGuard } from 'components/Basic/PageGuard/PageGuard';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { OrderDetailContent } from 'components/Pages/Customer/OrderDetail/OrderDetailContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    OrderAvailablePaymentsQueryDocument,
    TypeOrderAvailablePaymentsQueryVariables,
} from 'graphql/requests/orders/queries/OrderAvailablePaymentsQuery.generated';
import {
    OrderDetailQueryDocument,
    TypeOrderDetailQuery,
    TypeOrderDetailQueryVariables,
    useOrderDetailQuery,
} from 'graphql/requests/orders/queries/OrderDetailQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const OrderDetailPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], url);
    const router = useRouter();
    const orderNumber = getStringFromUrlQuery(router.query.orderNumber);
    const [{ data: orderData, fetching: isOrderDetailFetching, error: orderDetailError }] = useOrderDetailQuery({
        variables: { orderNumber },
    });
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl },
        { __typename: 'Link', name: orderNumber, slug: '' },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />

            <PageGuard errorRedirectUrl={customerOrdersUrl} isWithAccess={!orderDetailError}>
                <CustomerLayout
                    breadcrumbs={breadcrumbs}
                    breadcrumbsType="orderList"
                    isFetchingData={isOrderDetailFetching}
                >
                    {!!orderData?.order && <OrderDetailContent order={orderData.order} />}
                </CustomerLayout>
            </PageGuard>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t, ssrExchange }) =>
        async (context) => {
            if (typeof context.query.orderNumber !== 'string') {
                return {
                    redirect: {
                        destination: '/',
                        statusCode: 301,
                    },
                };
            }
            const client = createClient({
                t,
                ssrExchange,
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                redisClient,
                context,
            });

            const orderResponse: OperationResult<TypeOrderDetailQuery, TypeOrderDetailQueryVariables> = await client!
                .query(OrderDetailQueryDocument, {
                    orderNumber: context.query.orderNumber,
                })
                .toPromise();

            const orderUuid = orderResponse.data?.order?.uuid;

            return initServerSideProps<TypeOrderAvailablePaymentsQueryVariables>({
                authenticationConfig: {
                    authenticationRequired: true,
                    authorizedRoles: [
                        TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation,
                        TypeCustomerUserRoleEnum.RoleApiCompanyOrdersView,
                    ],
                },
                prefetchedQueries: orderUuid
                    ? [
                          {
                              query: OrderAvailablePaymentsQueryDocument,
                              variables: { orderUuid: orderUuid },
                          },
                      ]
                    : [],
                context,
                client,
                ssrExchange,
                domainConfig,
            });
        },
);

export default OrderDetailPage;
