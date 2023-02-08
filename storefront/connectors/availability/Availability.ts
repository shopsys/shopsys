import { mapStoreDetailApiData } from 'connectors/stores/StoreDetail';
import { StoreAvailabilityFragmentApi } from 'graphql/generated';
import { StoreAvailabilityType } from 'types/availability';

const mapStoreAvailability = (storeAvailabilityApiData: StoreAvailabilityFragmentApi): StoreAvailabilityType => {
    return {
        ...storeAvailabilityApiData,
        store: mapStoreDetailApiData(storeAvailabilityApiData.store!),
    };
};

export const mapStoreAvailabilities = (apiData: StoreAvailabilityFragmentApi[]): StoreAvailabilityType[] => {
    const mappedStoreAvailabilities = [];

    for (const storeAvailabilityApiData of apiData) {
        if (storeAvailabilityApiData.store !== null) {
            mappedStoreAvailabilities.push(mapStoreAvailability(storeAvailabilityApiData));
        }
    }

    return mappedStoreAvailabilities;
};
