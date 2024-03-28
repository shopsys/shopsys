import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';

export const getGtmPickupPlaceFromStore = (store: TypeListedStoreFragment): TypeListedStoreFragment => ({
    __typename: 'Store',
    locationLatitude: null,
    locationLongitude: null,
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
