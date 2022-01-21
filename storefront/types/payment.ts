import { PriceApiType, PriceType } from 'types/price';
import { ImageSizeType } from 'types/image';

export type PaymentInputType = {
    uuid: string;
    price: PriceApiType;
};

export type PaymentType = {
    uuid: string;
    name: string;
    description: string;
    instruction: string;
    price: PriceType;
    image: ImageSizeType | null;
};
