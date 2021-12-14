import { ImageType } from 'components/Basic/Image/types';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from './pickupPlace/types';

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

export type TransportInputType = {
    uuid: string;
    price: PriceApiType;
    pickupPlaceIdentifier: string | null;
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
    isPersonalPickup: boolean;
    stores: PickupPlaceType[];
    transportType: {
        code: string;
    };
};
