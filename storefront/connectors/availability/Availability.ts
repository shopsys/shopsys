import { AvailabilityFragmentApi, StoreAvailabilityFragmentApi } from 'graphql/generated';
import { AvailabilityStatusType, AvailabilityType, StoreAvailabilityType } from 'types/availability';
import { mapStoreDetailApiData } from 'connectors/stores/StoreDetail';

const mapAvailabilityStatus = (availabilityStatus: string): AvailabilityStatusType => {
    return (availabilityStatus === 'in-stock' ? 'in-stock' : 'out-of-stock') as AvailabilityStatusType;
};

export const mapAvailabilityData = (availabilityApiData: AvailabilityFragmentApi): AvailabilityType => {
    return {
        ...availabilityApiData,
        status: mapAvailabilityStatus(availabilityApiData.status),
    };
};

const mapStoreAvailability = (storeAvailabilityApiData: StoreAvailabilityFragmentApi): StoreAvailabilityType => {
    return {
        ...storeAvailabilityApiData,
        availabilityStatus: mapAvailabilityStatus(storeAvailabilityApiData.availabilityStatus),
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
