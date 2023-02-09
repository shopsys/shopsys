import { ImageSizesFragmentApi, PriceFragmentApi } from 'graphql/generated';
import { PriceType } from 'types/price';

export const PaymentTypeEnum = {
    GoPay: 'goPay',
} as const;

export type PaymentInputType = {
    uuid: string;
    price: PriceFragmentApi;
    goPayBankSwift: string | null;
};

export type GoPayPaymentMethodType = {
    identifier: string;
    name: string;
    paymentGroup: string;
};

export type PaymentType = {
    uuid: string;
    name: string;
    description: string;
    instruction: string;
    goPayPaymentMethod: GoPayPaymentMethodType | undefined;
    price: PriceType;
    images: ImageSizesFragmentApi[];
    type: string;
};
