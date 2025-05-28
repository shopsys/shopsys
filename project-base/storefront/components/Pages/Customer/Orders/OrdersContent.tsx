import { OrderItem } from './OrderItem';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerOrders } from 'components/Blocks/Skeleton/SkeletonModuleCustomerOrders';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';
import { useAddOrderItemsToCart } from 'utils/cart/useAddOrderItemsToCart';

type OrdersContentProps = {
    areOrdersFetching: boolean;
    orders: TypeListedOrderFragment[] | undefined;
    totalCount: number | undefined;
    hasNextPage: boolean | undefined;
};

export const OrdersContent: FC<OrdersContentProps> = ({ areOrdersFetching, orders, totalCount, hasNextPage }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const addOrderItemsToEmptyCart = useAddOrderItemsToCart();
    const { t } = useTranslation();

    if (areOrdersFetching) {
        return <SkeletonModuleCustomerOrders />;
    }

    if (!orders?.length) {
        return (
            <div className="vl:text-xl flex gap-2 text-lg">
                <InfoIcon className="w-5" />
                {t('You have no orders')}
            </div>
        );
    }

    return (
        <div className="flex scroll-mt-5 flex-col gap-5" ref={paginationScrollTargetRef}>
            {orders.map((order, index) => (
                <OrderItem
                    key={order.uuid}
                    addOrderItemsToEmptyCart={addOrderItemsToEmptyCart}
                    listIndex={index}
                    order={order}
                />
            ))}

            <PaginationProvider paginationScrollTargetRef={paginationScrollTargetRef}>
                <Pagination hasNextPage={hasNextPage} pageSize={DEFAULT_ORDERS_SIZE} totalCount={totalCount || 0} />
            </PaginationProvider>
        </div>
    );
};
