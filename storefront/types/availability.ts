import { StoreDetailType } from 'types/store';

export type AvailabilityStatusType = 'in-stock' | 'out-of-stock';

export type AvailabilityType = {
    name: string;
    status: AvailabilityStatusType;
};

export type StoreAvailabilityType = {
    exposed: boolean;
    availabilityInformation: string;
    availabilityStatus: AvailabilityStatusType;
    store: StoreDetailType;
};
