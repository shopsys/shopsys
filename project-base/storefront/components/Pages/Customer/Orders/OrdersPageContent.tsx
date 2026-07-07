import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { OrdersContent } from 'components/Pages/Customer/Orders/OrdersContent';
import { OrdersFilter } from 'components/Pages/Customer/Orders/OrdersFilter';
import { OrderStatusCount } from 'components/Pages/Customer/Orders/OrdersStatusTabs';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import { useOrdersQuery } from 'graphql/requests/orders/queries/OrdersQuery.generated';
import { useRouter } from 'next/router';
import { type RefObject } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';
import {
    getOrderStatusCodeFromUrlQuery,
    getOrdersFilterFromUrlQuery,
    getOrdersStatuslessFilterFromUrlQuery,
    hasActiveOrderListFiltersFromUrlQuery,
} from 'utils/orders/getOrdersFilterFromUrlQuery';
import { ORDER_STATUS_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';

type OrdersPageContentProps = {
    paginationScrollTargetRef: RefObject<HTMLDivElement | null>;
};

export const OrdersPageContent: FC<OrdersPageContentProps> = ({ paginationScrollTargetRef }) => {
    const router = useRouter();
    const currentPage = useCurrentPageQuery();
    const { fallbackTimezone } = useDomainConfig();
    const filter = getOrdersFilterFromUrlQuery(router.query, fallbackTimezone);
    const activeStatusCode = getOrderStatusCodeFromUrlQuery(router.query[ORDER_STATUS_QUERY_PARAMETER_NAME]);
    const hasActiveFilters = hasActiveOrderListFiltersFromUrlQuery(router.query);
    const statuslessFilter = getOrdersStatuslessFilterFromUrlQuery(router.query, fallbackTimezone);
    const [{ data: ordersData, fetching: areOrdersFetching }] = useOrdersQuery({
        variables: {
            after: getEndCursor(currentPage, 0, DEFAULT_ORDERS_SIZE),
            filter,
            first: DEFAULT_ORDERS_SIZE,
            statuslessFilter,
        },
        requestPolicy: 'cache-and-network',
    });
    const mappedOrders = mapConnectionEdges<TypeListedOrderFragment>(ordersData?.orders?.edges);
    const orderStatusCounts: OrderStatusCount[] = (ordersData?.orderStatusCounts ?? []).map(({ status, count }) => ({
        statusCode: status.code,
        label: status.name,
        count,
    }));

    return (
        <div className="flex scroll-mt-5 flex-col gap-5" ref={paginationScrollTargetRef}>
            <OrdersFilter orderStatusCounts={orderStatusCounts} />

            <OrdersContent
                areOrdersFetching={areOrdersFetching}
                filteredTotalCount={ordersData?.orders?.totalCount}
                hasActiveFilters={hasActiveFilters}
                hasActiveStatus={activeStatusCode !== null}
                hasNextPage={ordersData?.orders?.pageInfo.hasNextPage}
                orders={mappedOrders}
            />
        </div>
    );
};
