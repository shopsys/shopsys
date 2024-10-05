import { OrderItem } from './OrderItem';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerOrders } from 'components/Blocks/Skeleton/SkeletonModuleCustomerOrders';
import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';
import { useAddOrderItemsToCart } from 'utils/cart/useAddOrderItemsToCart';

type OrdersContentProps = {
    areOrdersFetching: boolean;
    orders: TypeListedOrderFragment[] | undefined;
    totalCount: number | undefined;
};

export const OrdersContent: FC<OrdersContentProps> = ({ areOrdersFetching, orders, totalCount }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const addOrderItemsToEmptyCart = useAddOrderItemsToCart();
    const { t } = useTranslation();

    if (areOrdersFetching) {
        return <SkeletonModuleCustomerOrders />;
    }

    if (!orders?.length) {
        return (
            <div className="flex gap-2 text-lg vl:text-xl">
                <InfoIcon className="w-5" />
                {t('You have no orders')}
            </div>
        );
    }

    return (
        <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
            <div className="flex flex-col gap-7">
                {orders.map((order, index) => (
                    <OrderItem
                        key={order.uuid}
                        addOrderItemsToEmptyCart={addOrderItemsToEmptyCart}
                        listIndex={index}
                        order={order}
                    />
                ))}
            </div>

            <Pagination paginationScrollTargetRef={paginationScrollTargetRef} totalCount={totalCount || 0} />
        </div>
    );
};
