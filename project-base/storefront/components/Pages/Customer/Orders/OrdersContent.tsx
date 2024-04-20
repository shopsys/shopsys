import { OrderItem } from './OrderItem';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerOrders } from 'components/Blocks/Skeleton/SkeletonModuleCustomerOrders';
import { Webline } from 'components/Layout/Webline/Webline';
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
        return (
            <Webline>
                <SkeletonModuleCustomerOrders />
            </Webline>
        );
    }

    if (!orders?.length) {
        return (
            <Webline className="text-lg vl:text-xl flex gap-2">
                <InfoIcon className="w-5" />
                {t('You have no orders')}
            </Webline>
        );
    }

    return (
        <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
            <Webline className="flex flex-col gap-7">
                {orders.map((order, index) => (
                    <OrderItem
                        key={order.uuid}
                        addOrderItemsToEmptyCart={addOrderItemsToEmptyCart}
                        listIndex={index}
                        order={order}
                    />
                ))}
            </Webline>

            <Webline>
                <Pagination paginationScrollTargetRef={paginationScrollTargetRef} totalCount={totalCount || 0} />
            </Webline>
        </div>
    );
};
