import { PaymentFail } from './PaymentFail';
import { PaymentInProcess } from './PaymentInProcess';
import { PaymentSuccess } from './PaymentSuccess';
import { TypeOrderDetailByHashQuery } from 'graphql/requests/orders/queries/OrderDetailByHashQuery.generated';
import { TypeOrderPaymentPageContentQuery } from 'graphql/requests/orders/queries/OrderPaymentPageContentQuery.generated';
import { TypePaymentContentPageStatusEnum } from 'graphql/types';

export const PaymentStatus: FC<{
    orderData: TypeOrderDetailByHashQuery | undefined;
    orderPaymentPageContentData: TypeOrderPaymentPageContentQuery | undefined;
}> = ({ orderData, orderPaymentPageContentData }) => {
    const order = orderData?.order;

    const isPaymentSuccessful =
        orderPaymentPageContentData?.orderPaymentPageContent.status === TypePaymentContentPageStatusEnum.Successful;
    const isPaymentInProcess =
        orderPaymentPageContentData?.orderPaymentPageContent.status === TypePaymentContentPageStatusEnum.InProcess;
    const isPaymentFailed =
        orderPaymentPageContentData?.orderPaymentPageContent.status === TypePaymentContentPageStatusEnum.Failed;

    if (order?.isPaid && isPaymentSuccessful) {
        return (
            <PaymentSuccess
                orderPaymentSuccessfulContent={orderPaymentPageContentData.orderPaymentPageContent.content}
            />
        );
    }

    if (order?.hasPaymentInProcess && isPaymentInProcess) {
        return (
            <PaymentInProcess
                orderPaymentInProcessContent={orderPaymentPageContentData.orderPaymentPageContent.content}
            />
        );
    }

    if (order && isPaymentFailed) {
        return <PaymentFail orderPaymentFailedContent={orderPaymentPageContentData.orderPaymentPageContent.content} />;
    }

    return null;
};
