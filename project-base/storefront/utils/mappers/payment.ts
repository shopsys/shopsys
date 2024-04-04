import { PaymentTypeEnum } from 'types/payment';

export const getIsPaymentWithPaymentGate = (paymentType: string) => paymentType === PaymentTypeEnum.GoPay;
