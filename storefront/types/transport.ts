import { ImageSizesFragmentApi, PriceFragmentApi, SimplePaymentFragmentApi } from 'graphql/generated';
import { PickupPlaceType } from 'types/pickupPlace';

export type TransportType = {
    uuid: string;
    name: string;
    description: string;
    instruction: string;
    price: PriceFragmentApi;
    images: ImageSizesFragmentApi[];
    payments: SimplePaymentFragmentApi[];
    daysUntilDelivery: number;
    isPersonalPickup: boolean;
    stores: PickupPlaceType[];
    transportType: {
        code: string;
    };
};
