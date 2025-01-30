import { PaymentFail } from './PaymentFail';
import { PaymentInProcess } from './PaymentInProcess';
import { PaymentSuccess } from './PaymentSuccess';
import { TypeUpdatePaymentStatusMutation } from 'graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated';
import { TypeOrderPaymentFailedContentQuery } from 'graphql/requests/orders/queries/OrderPaymentFailedContentQuery.generated';
import { TypeOrderPaymentSuccessfulContentQuery } from 'graphql/requests/orders/queries/OrderPaymentSuccessfulContentQuery.generated';

export const PaymentStatus: FC<{
    paymentStatusData: TypeUpdatePaymentStatusMutation | undefined;
    failedContentData: TypeOrderPaymentFailedContentQuery | undefined;
    successContentData: TypeOrderPaymentSuccessfulContentQuery | undefined;
}> = ({ paymentStatusData, failedContentData, successContentData }) => {
    if (paymentStatusData?.UpdatePaymentStatus.isPaid) {
        return successContentData ? (
            <PaymentSuccess orderPaymentSuccessfulContent={successContentData.orderPaymentSuccessfulContent} />
        ) : null;
    }

    if (paymentStatusData?.UpdatePaymentStatus.hasPaymentInProcess) {
        return <PaymentInProcess orderUrlHash={paymentStatusData.UpdatePaymentStatus.urlHash} />;
    }

    if (paymentStatusData && failedContentData) {
        return <PaymentFail orderPaymentFailedContent={failedContentData.orderPaymentFailedContent} />;
    }

    return null;
};
