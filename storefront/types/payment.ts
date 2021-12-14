import { PriceApiType, PriceType } from 'types/price';
import { ImageType } from 'components/Basic/Image/types';

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
    image: ImageType | null;
};
