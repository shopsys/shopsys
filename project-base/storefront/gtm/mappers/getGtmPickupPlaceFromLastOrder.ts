import { TypeLastOrderFragment } from 'graphql/requests/orders/fragments/LastOrderFragment.generated';
import { TypeStoreOpeningStatusEnum } from 'graphql/types';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

export const getGtmPickupPlaceFromLastOrder = (
    pickupPlaceIdentifier: string,
    lastOrder: TypeLastOrderFragment,
): StoreOrPacketeryPoint => ({
    __typename: 'Store',
    latitude: null,
    longitude: null,
    slug: '',
    identifier: pickupPlaceIdentifier,
    name: '',
    city: lastOrder.deliveryCity ?? '',
    country: {
        __typename: 'Country',
        name: lastOrder.deliveryCountry?.name ?? '',
        code: lastOrder.deliveryCountry?.code ?? '',
    },
    description: null,
    openingHours: {
        status: TypeStoreOpeningStatusEnum.Closed,
        dayOfWeek: 0,
        openingHoursOfDays: [],
    },
    postcode: lastOrder.deliveryPostcode ?? '',
    street: lastOrder.deliveryStreet ?? '',
    distance: null,
});
