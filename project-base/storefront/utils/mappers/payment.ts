import { TypePaymentTypeEnum } from 'graphql/types';

export const getIsPaymentWithPaymentGate = (paymentType: TypePaymentTypeEnum) =>
    paymentType === TypePaymentTypeEnum.GoPay;
