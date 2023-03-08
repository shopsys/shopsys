import { PaymentFail } from './PaymentFail';
import { PaymentSuccess } from './PaymentSuccess';

type PaymentConfirmationContentProps = {
    isSuccess: boolean;
    orderUuid: string;
};

export const PaymentConfirmationContent: FC<PaymentConfirmationContentProps> = ({ isSuccess, orderUuid }) =>
    isSuccess ? <PaymentSuccess orderUuid={orderUuid} /> : <PaymentFail />;
