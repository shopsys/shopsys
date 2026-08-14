import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';

export type DeliveryOptionsProduct = {
    uuid: string;
    fullName: string;
    mainImage?: TypeImageFragment | null;
    availability: { name: string; status: TypeAvailabilityStatusEnum };
    price: { priceWithVat: string };
};
