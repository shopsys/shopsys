import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerOrders } from 'components/Blocks/Skeleton/SkeletonModuleCustomerOrders';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { CustomerEmptyContent } from 'components/Pages/Customer/CustomerEmptyContent';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import { useAddOrderItemsToCart } from 'utils/cart/useAddOrderItemsToCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { OrderItem } from './OrderItem';

type OrdersContentProps = {
    areOrdersFetching: boolean;
    orders: TypeListedOrderFragment[] | undefined;
    filteredTotalCount: number | undefined;
    hasNextPage: boolean | undefined;
    hasActiveFilters: boolean;
    hasActiveStatus: boolean;
};

export const OrdersContent: FC<OrdersContentProps> = ({
    areOrdersFetching,
    orders,
    filteredTotalCount,
    hasNextPage,
    hasActiveFilters,
    hasActiveStatus,
}) => {
    const addOrderItemsToEmptyCart = useAddOrderItemsToCart();
    const { t } = useTranslation();

    if (areOrdersFetching || orders === undefined) {
        return <SkeletonModuleCustomerOrders />;
    }

    if (orders.length === 0) {
        return (
            <CustomerEmptyContent
                title={
                    hasActiveFilters || hasActiveStatus ? t('No orders match your filters') : t('You have no orders')
                }
                description={
                    hasActiveFilters || hasActiveStatus
                        ? t('Try adjusting or clearing your filters to see more orders.')
                        : t('Your order history will appear here after your first purchase.')
                }
            />
        );
    }

    return (
        <>
            <VerticalStack gap="sm">
                {orders.map((order, index) => (
                    <OrderItem
                        key={order.uuid}
                        addOrderItemsToEmptyCart={addOrderItemsToEmptyCart}
                        listIndex={index}
                        order={order}
                    />
                ))}
            </VerticalStack>

            <Pagination hasNextPage={hasNextPage} pageSize={DEFAULT_ORDERS_SIZE} totalCount={filteredTotalCount || 0} />
        </>
    );
};
