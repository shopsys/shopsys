import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { DocumentIcon } from 'components/Basic/Icon/DocumentIcon';
import { PageGuard } from 'components/Basic/PageGuard/PageGuard';
import { CURRENT_CUSTOMER_USER_REVIEWS_LIMIT } from 'components/Blocks/ProductReviews/useCurrentCustomerUserReviewedProductUuids';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { OrderDetailContent } from 'components/Pages/Customer/OrderDetail/OrderDetailContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { CurrentCustomerUserQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
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
import {
    CurrentCustomerUserProductReviewsQueryDocument,
    TypeCurrentCustomerUserProductReviewsQueryVariables,
} from 'graphql/requests/productReviews/queries/CurrentCustomerUserProductReviewsQuery.generated';
import {
    SettingsQueryDocument,
    TypeSettingsQuery,
    TypeSettingsQueryVariables,
} from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { getBaseUrlWithLocale } from 'utils/domain/domainUtils';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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
    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <>
            <MetaRobots content="noindex" />

            <PageGuard errorRedirectUrl={customerOrdersUrl} isWithAccess={!orderDetailError}>
                <CustomerLayout
                    breadcrumbs={breadcrumbs}
                    breadcrumbsType="orderList"
                    isFetchingData={isOrderDetailFetching}
                >
                    <PageHero
                        icon={DocumentIcon}
                        title={t('Your order') + (orderData?.order?.number ? ` ${orderData.order.number}` : '')}
                        titleTid={TIDs.order_detail_number_heading}
                    />

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
                        destination: getBaseUrlWithLocale('/', domainConfig.defaultLocale),
                        statusCode: 301,
                    },
                };
            }

            const client = createClient({
                t,
                ssrExchange,
                domainConfig,
                redisClient,
                context,
            });

            let orderUuid = null;
            let orderUrlHash = null;

            const settingsResult: OperationResult<TypeSettingsQuery, TypeSettingsQueryVariables> = await client
                .query(SettingsQueryDocument, {})
                .toPromise();
            const areProductReviewsEnabled = settingsResult.data?.settings?.productReviewsEnabled === true;

            const customerResult = await client.query(CurrentCustomerUserQueryDocument, {}).toPromise();

            if (customerResult.data?.currentCustomerUser) {
                const orderResponse: OperationResult<TypeOrderDetailQuery, TypeOrderDetailQueryVariables> = await client
                    .query(OrderDetailQueryDocument, {
                        orderNumber: context.query.orderNumber,
                    })
                    .toPromise();
                orderUuid = orderResponse.data?.order?.uuid;
                orderUrlHash = orderResponse.data?.order?.urlHash ?? null;
            }

            return initServerSideProps<
                TypeCurrentCustomerUserProductReviewsQueryVariables | TypeOrderAvailablePaymentsQueryVariables
            >({
                currentCustomerUserPrefetchMode: 'full',
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
                              variables: { orderUuid: orderUuid, orderUrlHash: orderUrlHash },
                          },
                          ...(areProductReviewsEnabled
                              ? [
                                    {
                                        query: CurrentCustomerUserProductReviewsQueryDocument,
                                        variables: { first: CURRENT_CUSTOMER_USER_REVIEWS_LIMIT },
                                    },
                                ]
                              : []),
                      ]
                    : [],
                context,
                client,
                redisClient,
                ssrExchange,
                domainConfig,
            });
        },
);

export default OrderDetailPage;
