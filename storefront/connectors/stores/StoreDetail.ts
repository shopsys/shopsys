import { StoreDetailFragmentApi } from 'graphql/generated';
import { StoreDetailType } from 'connectors/stores/types';

export const mapStoreDetailApiData = (data: StoreDetailFragmentApi): StoreDetailType => {
    return {
        ...data,
        __typename: 'Store',
        description: data.description !== undefined ? data.description : null,
        openingHours: data.openingHours !== undefined ? data.openingHours : null,
        contactInfo: data.contactInfo !== undefined ? data.contactInfo : null,
        specialMessage: data.specialMessage !== undefined ? data.specialMessage : null,
        locationLatitude:
            data.locationLatitude !== undefined && data.locationLatitude !== null
                ? Number.parseFloat(data.locationLatitude)
                : null,
        locationLongitude:
            data.locationLongitude !== undefined && data.locationLongitude !== null
                ? Number.parseFloat(data.locationLongitude)
                : null,
    };
};
