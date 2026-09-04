import { TypeAvailabilityStatusEnum } from 'graphql/types';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const getInStockAvailabilityDetails = (
    availabilityStatus: TypeAvailabilityStatusEnum,
    availableStoresCount: number | null,
    isPersonalPickupOnly: boolean,
    t: ReturnType<typeof useTranslation>['t'],
): string | null => {
    if (availabilityStatus !== TypeAvailabilityStatusEnum.InStock || availableStoresCount === null) {
        return null;
    }

    // a personal pickup only product stocked outside the stores has to be transferred first, so nothing is "ready"
    if (isPersonalPickupOnly && availableStoresCount === 0) {
        return null;
    }

    const storeCountText =
        availableStoresCount > 0 ? `\u00a0·\u00a0${t('{{ count }} stores', { count: availableStoresCount })}` : '';

    return `${isPersonalPickupOnly ? t('Ready for pickup') : t('Ready to ship')}${storeCountText}`;
};
