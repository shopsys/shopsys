import { ImageSizeType } from 'types/image';
import { PriceFragmentApi } from 'graphql/generated';
import { PriceType } from 'types/price';

export type PaymentInputType = {
    uuid: string;
    price: PriceFragmentApi;
};

export type PaymentType = {
    uuid: string;
    name: string;
    description: string;
    instruction: string;
    price: PriceType;
    image: ImageSizeType | null;
};
