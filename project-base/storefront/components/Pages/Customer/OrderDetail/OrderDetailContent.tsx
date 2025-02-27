import { OrderDetailBasicInfo } from './OrderDetailBasicInfo';
import { OrderCustomerInfo } from 'components/Blocks/OrderCustomerInfo/OrderCustomerInfo';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { OrderPaymentStatusBar } from 'components/Pages/Customer/Orders/OrderPaymentStatusBar';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { PaymentTypeEnum } from 'types/payment';

type OrderDetailContentProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailContent: FC<OrderDetailContentProps> = ({ order }) => {
    const { canCreateOrder } = useAuthorization();
    const { t } = useTranslation();

    const orderHeading = `${t('Order number')} ${order.number}`;

    return (
        <VerticalStack gap="sm">
            <h1 tid={TIDs.order_detail_number_heading}>{orderHeading}</h1>

            <OrderPaymentStatusBar
                orderHasPaymentInProcess={order.hasPaymentInProcess}
                orderIsPaid={order.isPaid}
                orderPaymentType={order.payment.type}
            />

            {canCreateOrder &&
                order.payment.type === PaymentTypeEnum.GoPay &&
                !order.isPaid &&
                !order.hasPaymentInProcess && (
                    <div>
                        <PaymentsInOrderSelect
                            orderUuid={order.uuid}
                            paymentTransactionCount={order.paymentTransactionsCount}
                        />
                    </div>
                )}

            <OrderDetailBasicInfo order={order} />

            <OrderCustomerInfo order={order} />
        </VerticalStack>
    );
};
