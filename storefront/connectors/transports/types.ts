import { ImageApiType, ImageType } from 'components/Basic/Image/types';

export type PriceApiType = {
    priceWithVat: string;
    priceWithoutVat: string;
    vatAmount: string;
};
export type PriceType = {
    priceWithVat: number;
    priceWithoutVat: number;
    vatAmount: number;
    currencyCode: string;
};

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

export type StoreType = {
    uuid: string;
    name: string;
    description: string;
    openingHours: string;
    street: string;
    postcode: string;
    city: string;
};

export type TransportApiType = {
    uuid: string;
    name: string;
    description: string;
    instruction: string;
    price: PriceApiType;
    images: ImageApiType[];
    payments: PaymentApiType[];
    daysUntilDelivery: number;
    stores: {
        edges: {
            node: StoreType;
        }[];
    };
};

export type TransportType = {
    uuid: string;
    name: string;
    description: string;
    instruction: string;
    price: PriceType;
    image: ImageType | null;
    payments: PaymentType[];
    daysUntilDelivery: number;
    personalPickup: boolean;
    stores: StoreType[];
};
