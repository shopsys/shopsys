import { OrderDetailBasicInfo } from './OrderDetailBasicInfo';
import { OrderDetailCustomerInfo } from './OrderDetailCustomerInfo';
import { OrderPaymentStatusBar } from 'components/Pages/Customer/Orders/OrderPaymentStatusBar';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { TypePaymentTypeEnum } from 'graphql/types';

type OrderDetailContentProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailContent: FC<OrderDetailContentProps> = ({ order }) => {
    return (
        <div>
            <OrderPaymentStatusBar
                orderHasPaymentInProcess={order.hasPaymentInProcess}
                orderIsPaid={order.isPaid}
                orderPaymentType={order.payment.type}
            />
            {order.payment.type === TypePaymentTypeEnum.GoPay && !order.isPaid && !order.hasPaymentInProcess && (
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
