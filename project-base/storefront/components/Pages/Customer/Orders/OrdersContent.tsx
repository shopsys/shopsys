import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerOrders } from 'components/Blocks/Skeleton/SkeletonModuleCustomerOrders';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import { useAddOrderItemsToCart } from 'utils/cart/useAddOrderItemsToCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { OrderItem } from './OrderItem';

type OrdersContentProps = {
    areOrdersFetching: boolean;
    orders: TypeListedOrderFragment[] | undefined;
    totalCount: number | undefined;
    hasNextPage: boolean | undefined;
};

export const OrdersContent: FC<OrdersContentProps> = ({ areOrdersFetching, orders, totalCount, hasNextPage }) => {
    const addOrderItemsToEmptyCart = useAddOrderItemsToCart();
    const { t } = useTranslation();

    if (!orders?.length && !areOrdersFetching) {
        return (
            <div className="flex gap-2 text-lg vl:text-xl">
                <InfoIcon className="w-5" />
                {t('You have no orders')}
            </div>
        );
    }

    return (
        <>
            {areOrdersFetching ? (
                <SkeletonModuleCustomerOrders />
            ) : (
                <div className="flex flex-col gap-5">
                    {orders?.map((order, index) => (
                        <OrderItem
                            key={order.uuid}
                            addOrderItemsToEmptyCart={addOrderItemsToEmptyCart}
                            listIndex={index}
                            order={order}
                        />
                    ))}
                </div>
            )}

            <Pagination hasNextPage={hasNextPage} pageSize={DEFAULT_ORDERS_SIZE} totalCount={totalCount || 0} />
        </>
    );
};
