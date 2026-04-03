import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { PageGuard } from 'components/Basic/PageGuard/PageGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderWithdrawalContent } from 'components/Pages/OrderWithdrawal/OrderWithdrawalContent';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    OrderWithdrawalDataQueryDocument,
    TypeOrderWithdrawalDataQuery,
    TypeOrderWithdrawalDataQueryVariables,
    useOrderWithdrawalDataQuery,
} from 'graphql/requests/orders/queries/OrderWithdrawalDataQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { getBasePathWithLocale } from 'utils/domain/domainUtils';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const OrderWithdrawalPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const router = useRouter();
    const orderHash = getStringFromUrlQuery(router.query.orderUrlHash);
    const { canRequestWithdrawal: userCanRequestWithdrawal } = useAuthorization();

    const [{ data: orderData, fetching: isOrderFetching }] = useOrderWithdrawalDataQuery({
        variables: { urlHash: orderHash },
    });

    const [orderDetailUrl] = getInternationalizedStaticUrls([{ url: '/order-detail/:urlHash', param: orderHash }], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('Order detail'), slug: orderDetailUrl },
        { __typename: 'Link', name: t('Withdrawal from contract'), slug: '' },
    ];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const hasAccess = userCanRequestWithdrawal || isOrderFetching;

    return (
        <>
            <MetaRobots content="noindex" />
            <PageGuard errorRedirectUrl={orderDetailUrl} isWithAccess={hasAccess}>
                <CommonLayout
                    breadcrumbs={breadcrumbs}
                    isFetchingData={isOrderFetching}
                    pageTypeOverride="order-withdrawal"
                    title={t('Withdrawal from contract')}
                >
                    {!!orderData?.order && <OrderWithdrawalContent order={orderData.order} />}
                </CommonLayout>
            </PageGuard>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t, ssrExchange }) =>
        async (context) => {
            if (typeof context.params?.orderUrlHash !== 'string') {
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

            const orderResponse: OperationResult<TypeOrderWithdrawalDataQuery, TypeOrderWithdrawalDataQueryVariables> =
                await client
                    ?.query(OrderWithdrawalDataQueryDocument, {
                        urlHash: context.params.orderUrlHash,
                    })
                    .toPromise();

            if (!orderResponse.data?.order) {
                return {
                    redirect: {
                        destination: getBasePathWithLocale('/', context),
                        statusCode: 301,
                    },
                };
            }

            return initServerSideProps({
                context,
                client,
                ssrExchange,
                domainConfig,
                authenticationConfig: {
                    authenticationRequired: orderResponse.data.order.customerUser !== null,
                    authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
                },
            });
        },
);

export default OrderWithdrawalPage;
