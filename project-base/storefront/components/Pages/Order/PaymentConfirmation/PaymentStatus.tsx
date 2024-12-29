import { PaymentFail } from './PaymentFail';
import { PaymentInProcess } from './PaymentInProcess';
import { PaymentSuccess } from './PaymentSuccess';
import { TypeOrderDetailByHashQuery } from 'graphql/requests/orders/queries/OrderDetailByHashQuery.generated';
import { TypeOrderPaymentFailedContentQuery } from 'graphql/requests/orders/queries/OrderPaymentFailedContentQuery.generated';
import { TypeOrderPaymentSuccessfulContentQuery } from 'graphql/requests/orders/queries/OrderPaymentSuccessfulContentQuery.generated';

export const PaymentStatus: FC<{
    orderData: TypeOrderDetailByHashQuery | undefined;
    failedContentData: TypeOrderPaymentFailedContentQuery | undefined;
    successContentData: TypeOrderPaymentSuccessfulContentQuery | undefined;
}> = ({ orderData, failedContentData, successContentData }) => {
    const order = orderData?.order;
    if (order?.isPaid) {
        return successContentData ? (
            <PaymentSuccess orderPaymentSuccessfulContent={successContentData.orderPaymentSuccessfulContent} />
        ) : null;
    }

    if (order?.hasPaymentInProcess) {
        return <PaymentInProcess orderUrlHash={order.urlHash} />;
    }

    if (order && failedContentData) {
        return <PaymentFail orderPaymentFailedContent={failedContentData.orderPaymentFailedContent} />;
    }

    return null;
};
