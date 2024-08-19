import { StoreOrPacketeryPoint } from 'utils/packetery/types';

export const getGtmPickupPlaceFromStore = (store: StoreOrPacketeryPoint): StoreOrPacketeryPoint => ({
    __typename: 'Store',
    latitude: null,
    longitude: null,
    slug: '',
    identifier: store.identifier,
    name: store.name,
    city: store.city,
    country: {
        __typename: 'Country',
        name: store.country.name,
        code: store.country.code,
    },
    description: store.description ?? '',
    openingHours: store.openingHours,
    postcode: store.postcode,
    street: store.street,
});
