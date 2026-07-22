import { TypeAvailabilityStatusEnum } from 'graphql/types';

const availabilityTextColorClassNameByStatus: Record<TypeAvailabilityStatusEnum, string> = {
    [TypeAvailabilityStatusEnum.InStock]: 'text-availability-in-stock',
    [TypeAvailabilityStatusEnum.OutOfStock]: 'text-availability-out-of-stock',
    [TypeAvailabilityStatusEnum.ExpectedRestock]: 'text-availability-expected-restock',
};

export const getAvailabilityTextColorClassName = (availabilityStatus: TypeAvailabilityStatusEnum): string =>
    availabilityTextColorClassNameByStatus[availabilityStatus];
