import { TypeAvailabilityStatusEnum } from 'graphql/types';
import type { WatchDogProductType } from 'types/product';

export const showWatchdogButton = (product: WatchDogProductType): boolean =>
    !!product.uuid &&
    !product.isSellingDenied &&
    !product.isInquiryType &&
    (product.availability.status === TypeAvailabilityStatusEnum.OutOfStock ||
        product.availability.status === TypeAvailabilityStatusEnum.ExpectedRestock);
