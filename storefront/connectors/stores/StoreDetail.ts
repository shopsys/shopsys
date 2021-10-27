import { StoreDetailApiType, StoreDetailType } from 'connectors/stores/types';

export const storeDetailBody = `
    uuid
    storeName: name
    description
    street
    city
    postcode
    country
    openingHours
    contactInfo
    specialMessage
    locationLatitude
    locationLongitude
    breadcrumb {
        name
        slug
    }
` as const;

export function mapStoreDetailApiData(data: StoreDetailApiType): StoreDetailType {
    return {
        ...data,
        locationLatitude: data.locationLatitude !== null ? Number.parseFloat(data.locationLatitude) : null,
        locationLongitude: data.locationLongitude !== null ? Number.parseFloat(data.locationLongitude) : null,
    };
}
