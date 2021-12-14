import { ImageType } from 'types/image';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { PriceApiType, PriceType } from 'types/price';

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
