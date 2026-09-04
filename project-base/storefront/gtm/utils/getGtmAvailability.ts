import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { GtmProductAvailabilityType } from 'gtm/types/objects';

const gtmAvailabilityByStatus: Record<TypeAvailabilityStatusEnum, GtmProductAvailabilityType> = {
    [TypeAvailabilityStatusEnum.InStock]: 'in_stock',
    [TypeAvailabilityStatusEnum.OutOfStock]: 'out_of_stock',
    [TypeAvailabilityStatusEnum.ExpectedRestock]: 'expected_restock',
    [TypeAvailabilityStatusEnum.Digital]: 'in_stock',
};

export const getGtmAvailability = (availabilityStatus: TypeAvailabilityStatusEnum): GtmProductAvailabilityType =>
    gtmAvailabilityByStatus[availabilityStatus];
