import { ImageType } from 'types/image';
import { PriceFragmentApi } from 'graphql/generated';
import { PriceType } from 'types/price';

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
    goPayBankSwift: string | null;
    price: PriceType;
    image: ImageType | null;
    type: string;
};
