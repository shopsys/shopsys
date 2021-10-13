import { ImageApiType, ImageType } from 'components/Basic/Image/types';
import { PriceApiType, PriceType } from 'connectors/transports/types';

export type PaymentInputType = { uuid: string; price: PriceApiType };

export type PaymentApiType = {
    uuid: string;
    name: string;
    description: string;
    instruction: string;
    price: PriceApiType;
    images: ImageApiType[];
};

export type PaymentType = {
    uuid: string;
    name: string;
    description: string;
    instruction: string;
    price: PriceType;
    image: ImageType | null;
};
