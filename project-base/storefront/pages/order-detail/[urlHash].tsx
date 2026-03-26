import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderDetailByHashPageContent } from 'components/Pages/Customer/OrderDetail/OrderDetailByHashPageContent';
import { OrderPaymentRecoveryContent } from 'components/Pages/Customer/OrderDetail/OrderPaymentRecoveryContent';
import { useOrderDetailGoPayRecovery } from 'components/Pages/Customer/OrderDetail/useOrderDetailGoPayRecovery';
import { Error404Content } from 'components/Pages/ErrorPage/Error404Content';
import { useRefreshOrderPaymentStatus } from 'components/Pages/Order/PaymentConfirmation/useRefreshOrderPaymentStatus';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
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
    const domainConfig = useDomainConfig();
    const { url } = domainConfig;
    const router = useRouter();
    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl },
    ];
    const orderHash = getStringFromUrlQuery(router.query.urlHash);
    const [{ data: orderData, fetching: isOrderFetching }, reexecuteOrderDetailQuery] = useOrderDetailByHashQuery({
        variables: { urlHash: orderHash },
    });
    const order = orderData?.order ?? undefined;
    const isOrderDataFetching = isOrderFetching && !order;
    const isOrderMissing = !isOrderDataFetching && !order;
    const isRecoveringGoPaySession = useOrderDetailGoPayRecovery(domainConfig, order?.uuid);

    useRefreshOrderPaymentStatus(order, () => {
        reexecuteOrderDetailQuery({ requestPolicy: 'network-only' });
    });

    if (isRecoveringGoPaySession) {
        return (
            <>
                <MetaRobots content="noindex" />

                <CommonLayout>
                    <OrderPaymentRecoveryContent />
                </CommonLayout>
            </>
        );
    }

    if (isOrderMissing) {
        return (
            <>
                <MetaRobots content="noindex" />

                <Error404Content />
            </>
        );
    }

    return (
        <>
            <MetaRobots content="noindex" />

            <OrderDetailByHashPageContent
                breadcrumbs={breadcrumbs}
                isOrderDataFetching={isOrderDataFetching}
                order={order}
            />
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

export default OrderDetailByHashPage;
