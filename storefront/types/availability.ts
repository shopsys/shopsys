import { AvailabilityStatusEnumApi } from 'graphql/generated';
import { StoreDetailType } from 'types/store';

export type StoreAvailabilityType = {
    exposed: boolean;
    availabilityInformation: string;
    availabilityStatus: AvailabilityStatusEnumApi;
    store: StoreDetailType;
};
