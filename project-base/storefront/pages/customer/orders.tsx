import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { OrderIcon } from 'components/Basic/Icon/OrderIcon';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { OrdersPageContent } from 'components/Pages/Customer/Orders/OrdersPageContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { OrdersQueryDocument, TypeOrdersQueryVariables } from 'graphql/requests/orders/queries/OrdersQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import {
    getOrdersFilterFromUrlQuery,
    getOrdersStatuslessFilterFromUrlQuery,
} from 'utils/orders/getOrdersFilterFromUrlQuery';
import { getNumberFromUrlQuery } from 'utils/parsing/getNumberFromUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const OrdersPage: FC = () => {
    const { t } = useTranslation();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { url } = useDomainConfig();
    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl },
    ];

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <>
            <MetaRobots content="noindex" />

            <CustomerLayout
                breadcrumbs={breadcrumbs}
                paginationScrollTargetRef={paginationScrollTargetRef}
                title={t('My orders')}
            >
                <PageHero
                    icon={OrderIcon}
                    title={t('My orders')}
                    description={t(
                        'View and manage your past orders, track order status, and monitor your shopping history.',
                    )}
                />

                <OrdersPageContent paginationScrollTargetRef={paginationScrollTargetRef} />
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
                    filter: getOrdersFilterFromUrlQuery(context.query, domainConfig.fallbackTimezone),
                    first: DEFAULT_ORDERS_SIZE,
                    statuslessFilter: getOrdersStatuslessFilterFromUrlQuery(
                        context.query,
                        domainConfig.fallbackTimezone,
                    ),
                },
            },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default OrdersPage;
