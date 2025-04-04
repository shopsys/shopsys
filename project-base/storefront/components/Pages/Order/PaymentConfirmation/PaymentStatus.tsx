import { PaymentFail } from './PaymentFail';
import { PaymentInProcess } from './PaymentInProcess';
import { PaymentSuccess } from './PaymentSuccess';
import { TypeOrderDetailByHashQuery } from 'graphql/requests/orders/queries/OrderDetailByHashQuery.generated';
import { TypeOrderPaymentFailedContentQuery } from 'graphql/requests/orders/queries/OrderPaymentFailedContentQuery.generated';
import { TypeOrderPaymentInProcessContentQuery } from 'graphql/requests/orders/queries/OrderPaymentInProcessContentQuery.generated';
import { TypeOrderPaymentSuccessfulContentQuery } from 'graphql/requests/orders/queries/OrderPaymentSuccessfulContentQuery.generated';

export const PaymentStatus: FC<{
    orderData: TypeOrderDetailByHashQuery | undefined;
    failedContentData: TypeOrderPaymentFailedContentQuery | undefined;
    successContentData: TypeOrderPaymentSuccessfulContentQuery | undefined;
    inProcessContentData: TypeOrderPaymentInProcessContentQuery | undefined;
}> = ({ orderData, failedContentData, successContentData, inProcessContentData }) => {
    const order = orderData?.order;

    if (order?.isPaid) {
        return successContentData ? (
            <PaymentSuccess orderPaymentSuccessfulContent={successContentData.orderPaymentSuccessfulContent} />
        ) : null;
    }

    if (order?.hasPaymentInProcess) {
        return inProcessContentData ? (
            <PaymentInProcess orderPaymentInProcessContent={inProcessContentData.orderPaymentInProcessContent} />
        ) : null;
    }

    if (order && failedContentData) {
        return <PaymentFail orderPaymentFailedContent={failedContentData.orderPaymentFailedContent} />;
    }

    return null;
};
