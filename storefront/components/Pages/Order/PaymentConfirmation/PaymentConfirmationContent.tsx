import { PaymentFail } from './PaymentFail';
import { PaymentSuccess } from './PaymentSuccess';
import { FC } from 'react';

type PaymentConfirmationContentProps = {
    isSuccess: boolean;
    orderUuid: string;
};

export const PaymentConfirmationContent: FC<PaymentConfirmationContentProps> = ({ isSuccess, orderUuid }) => {
    return isSuccess ? <PaymentSuccess orderUuid={orderUuid} /> : <PaymentFail />;
};
