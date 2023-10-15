import { PaymentFail } from './PaymentFail';
import { PaymentSuccess } from './PaymentSuccess';

type PaymentConfirmationContentProps = {
    isPaid: boolean;
    orderUuid: string;
    orderPaymentType: string;
    canPaymentBeRepeated: boolean;
};

export const PaymentConfirmationContent: FC<PaymentConfirmationContentProps> = ({
    isPaid,
    orderUuid,
    orderPaymentType,
    canPaymentBeRepeated,
}) =>
    isPaid ? (
        <PaymentSuccess orderUuid={orderUuid} />
    ) : (
        <PaymentFail
            canPaymentBeRepeated={canPaymentBeRepeated}
            orderPaymentType={orderPaymentType}
            orderUuid={orderUuid}
        />
    );
