import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { TypeOrderConfirmationPageContentStatusEnum } from 'graphql/types';
import { PaymentFail } from './PaymentFail';
import { PaymentInProcess } from './PaymentInProcess';
import { PaymentSuccess } from './PaymentSuccess';

export const PaymentStatus: FC<{
    order: TypeOrderDetailFragment;
}> = ({ order }) => {
    const status = order.confirmationPageContent.status;
    const content = order.confirmationPageContent.content;

    const isPaymentSuccessful = status === TypeOrderConfirmationPageContentStatusEnum.Successful;
    const isPaymentInProcess = status === TypeOrderConfirmationPageContentStatusEnum.InProcess;
    const isPaymentFailed = status === TypeOrderConfirmationPageContentStatusEnum.Failed;

    if (order.isPaid && isPaymentSuccessful) {
        return <PaymentSuccess orderPaymentSuccessfulContent={content} />;
    }

    if (order.hasPaymentInProcess && isPaymentInProcess) {
        return (
            <PaymentInProcess
                orderPaymentInProcessContent={content}
                paymentInstructionUrl={order.lastExternalPaymentUrl}
            />
        );
    }

    if (isPaymentFailed) {
        return <PaymentFail orderPaymentFailedContent={content} />;
    }

    return null;
};
