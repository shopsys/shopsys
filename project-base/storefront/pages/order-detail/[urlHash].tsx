import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { DocumentIcon } from 'components/Basic/Icon/DocumentIcon';
import { PageGuard } from 'components/Basic/PageGuard/PageGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { OrderDetailContent } from 'components/Pages/Customer/OrderDetail/OrderDetailContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    OrderAvailablePaymentsQueryDocument,
    TypeOrderAvailablePaymentsQueryVariables,
} from 'graphql/requests/orders/queries/OrderAvailablePaymentsQuery.generated';
import {
    OrderDetailByHashQueryDocument,
    TypeOrderDetailByHashQuery,
    TypeOrderDetailByHashQueryVariables,
    useOrderDetailByHashQuery,
} from 'graphql/requests/orders/queries/OrderDetailByHashQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { getBasePathWithLocale } from 'utils/domain/domainUtils';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const OrderDetailByHashPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const router = useRouter();
    const orderHash = getStringFromUrlQuery(router.query.urlHash);
    const [{ data: orderData, fetching: isOrderFetching }] = useOrderDetailByHashQuery({
        variables: { urlHash: orderHash },
    });

    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl },
    ];

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <PageGuard errorRedirectUrl="/" isWithAccess={!!orderData?.order || isOrderFetching}>
                <CommonLayout
                    breadcrumbs={breadcrumbs}
                    title={`${t('Order number')} ${orderData?.order?.number ?? ''}`}
                >
                    {!!orderData?.order && (
                        <Webline width="lg">
                            <VerticalStack gap="sm">
                                <PageHero
                                    icon={DocumentIcon}
                                    title={`${t('Your order')} ${orderData.order.number}`}
                                    titleTid={TIDs.order_detail_number_heading}
                                />

                                <OrderDetailContent order={orderData.order} />
                            </VerticalStack>
                        </Webline>
                    )}
                </CommonLayout>
            </PageGuard>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t, ssrExchange }) =>
        async (context) => {
            if (typeof context.params?.urlHash !== 'string') {
                return {
                    redirect: {
                        destination: getBasePathWithLocale('/', context),
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

            const orderResponse: OperationResult<TypeOrderDetailByHashQuery, TypeOrderDetailByHashQueryVariables> =
                await client
                    ?.query(OrderDetailByHashQueryDocument, {
                        urlHash: context.params.urlHash,
                    })
                    .toPromise();

            const orderUuid = orderResponse.data?.order?.uuid;

            return initServerSideProps<TypeOrderAvailablePaymentsQueryVariables>({
                currentCustomerUserPrefetchMode: 'full',
                prefetchedQueries: orderUuid
                    ? [
                          {
                              query: OrderAvailablePaymentsQueryDocument,
                              variables: { orderUuid: orderUuid, orderUrlHash: context.params.urlHash },
                          },
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

export default OrderDetailByHashPage;
