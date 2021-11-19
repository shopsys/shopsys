import { ImageType } from 'components/Basic/Image/types';
import { PaymentType } from 'connectors/payments/types';

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

export type TransportInputType = { uuid: string; price: PriceApiType; pickupPlaceIdentifier: string | null };

export type StoreType = {
    uuid: string;
    name: string;
    description: string;
    openingHours: string;
    street: string;
    postcode: string;
    city: string;
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
    hasPersonalPickup: boolean;
    stores: StoreType[];
};
