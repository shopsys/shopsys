import { OrderDetailBasicInfo } from './OrderDetailBasicInfo';
import { OrderDetailCustomerInfo } from './OrderDetailCustomerInfo';
import { OrderPaymentStatusBar } from 'components/Pages/Customer/Orders/OrderPaymentStatusBar';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';

type OrderDetailContentProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailContent: FC<OrderDetailContentProps> = ({ order }) => {
    return (
        <div>
            <OrderPaymentStatusBar
                orderHasExternalPayment={order.hasExternalPayment}
                orderHasPaymentInProcess={order.hasPaymentInProcess}
                orderIsPaid={order.isPaid}
            />
            {order.hasExternalPayment && !order.isPaid && (
                <div>
                    <PaymentsInOrderSelect
                        orderUuid={order.uuid}
                        paymentTransactionCount={order.paymentTransactionsCount}
                    />
                </div>
            )}
            <OrderDetailBasicInfo order={order} />
            <OrderDetailCustomerInfo order={order} />
        </div>
    );
};
