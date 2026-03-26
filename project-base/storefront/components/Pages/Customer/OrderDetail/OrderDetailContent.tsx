import { OrderCustomerInfo } from 'components/Blocks/OrderCustomerInfo/OrderCustomerInfo';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { useEmitPendingPaymentEvent } from 'gtm/hooks/useEmitPendingPaymentEvent';
import { useEffect } from 'react';
import { getOrderPaymentItem } from 'utils/mappers/order';
import { OrderDetailBasicInfo } from './OrderDetailBasicInfo';
import { OrderDetailWithdrawalSection } from './OrderDetailWithdrawalSection';

type OrderDetailContentProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailContent: FC<OrderDetailContentProps> = ({ order }) => {
    const { tryEmitPaymentEvent } = useEmitPendingPaymentEvent();
    const orderPaymentItem = getOrderPaymentItem(order.items);

    useEffect(() => {
        tryEmitPaymentEvent({
            orderUuid: order.uuid,
            isPaid: order.isPaid,
            hasPaymentInProcess: order.hasPaymentInProcess,
            paymentTransactionsCount: order.paymentTransactionsCount,
            paymentName: orderPaymentItem?.name ?? '',
            orderNumber: order.number,
        });
    }, [
        order.hasPaymentInProcess,
        order.isPaid,
        order.number,
        order.paymentTransactionsCount,
        order.uuid,
        orderPaymentItem?.name,
        tryEmitPaymentEvent,
    ]);

    return (
        <>
            <OrderDetailWithdrawalSection order={order} />

            <OrderDetailBasicInfo order={order} />

            <OrderCustomerInfo order={order} />
        </>
    );
};
