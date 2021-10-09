import { ImageApiType, ImageType } from 'components/Basic/Image/types';
import { PaymentApiType, PaymentType } from 'connectors/payments/types';

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

export type TransportInputType = { uuid: string; price: PriceApiType; personalPickupStoreUuid: string | null };

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
