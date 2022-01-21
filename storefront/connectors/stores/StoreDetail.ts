import { StoreDetailFragmentApi } from 'graphql/generated';
import { StoreDetailType } from 'types/store';

export const mapStoreDetailApiData = (data: StoreDetailFragmentApi): StoreDetailType => {
    return {
        ...data,
        __typename: 'Store',
        locationLatitude: data.locationLatitude !== null ? Number.parseFloat(data.locationLatitude) : null,
        locationLongitude: data.locationLongitude !== null ? Number.parseFloat(data.locationLongitude) : null,
    };
};
