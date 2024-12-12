import { OrderDetailBasicInfo } from './OrderDetailBasicInfo';
import { OrderCustomerInfo } from 'components/Blocks/OrderCustomerInfo/OrderCustomerInfo';
import { OrderPaymentStatusBar } from 'components/Pages/Customer/Orders/OrderPaymentStatusBar';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';

type OrderDetailContentProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailContent: FC<OrderDetailContentProps> = ({ order }) => {
    const { canCreateOrder } = useAuthorization();

    return (
        <div>
            <OrderPaymentStatusBar
                orderHasExternalPayment={order.hasExternalPayment}
                orderHasPaymentInProcess={order.hasPaymentInProcess}
                orderIsPaid={order.isPaid}
            />
            {canCreateOrder &&
                order.hasExternalPayment && !order.isPaid && (
                    <div>
                        <PaymentsInOrderSelect
                            orderUuid={order.uuid}
                            paymentTransactionCount={order.paymentTransactionsCount}
                        />
                    </div>
                )}
            <OrderDetailBasicInfo order={order} />
            <OrderCustomerInfo order={order} />
        </div>
    );
};
