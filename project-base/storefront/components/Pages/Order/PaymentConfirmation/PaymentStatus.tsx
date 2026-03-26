import { TypeUpdatePaymentStatusMutation } from 'graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated';
import { TypeOrderDetailByHashQuery } from 'graphql/requests/orders/queries/OrderDetailByHashQuery.generated';
import { TypePaymentContentPageStatusEnum } from 'graphql/types';
import { PaymentFail } from './PaymentFail';
import { PaymentInProcess } from './PaymentInProcess';
import { PaymentSuccess } from './PaymentSuccess';

export const PaymentStatus: FC<{
    orderData: TypeOrderDetailByHashQuery | undefined;
    paymentStatusData: TypeUpdatePaymentStatusMutation | undefined;
    statusOverride?: TypePaymentContentPageStatusEnum;
}> = ({ orderData, paymentStatusData, statusOverride }) => {
    const order = orderData?.order;
    const paymentContent = paymentStatusData?.UpdatePaymentStatus.paymentPageContent;
    const status = statusOverride ?? paymentContent?.status;
    const paymentContentText = paymentContent?.content ?? '';

    if (!order || !status) {
        return null;
    }

    switch (status) {
        case TypePaymentContentPageStatusEnum.Successful:
            return <PaymentSuccess orderPaymentSuccessfulContent={paymentContentText} />;
        case TypePaymentContentPageStatusEnum.InProcess:
            return <PaymentInProcess orderPaymentInProcessContent={paymentContentText} />;
        default:
            return <PaymentFail orderPaymentFailedContent={paymentContentText} />;
    }
};
