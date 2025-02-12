'use client';

import { ProductAvailability } from 'app/_components/Blocks/Product/ProductAvailability';
import { ProductDetailAvailabilityList } from 'app/_components/Page/ProductDetail/ProductDetailAvailabilityList';
import { Popup } from 'components/Layout/Popup/Popup';
import { useTranslation } from 'components/providers/TranslationProvider';
import { TypeStoreAvailabilityFragment } from 'graphql/requests/storeAvailabilities/fragments/StoreAvailabilityFragment.ssr';
import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';

type ProductDetailContentProps = {
    isSellingDenied: boolean;
    availability: TypeAvailability;
    availableStoresCount: number | null;
    isInquiryType: boolean;
    storeAvailabilities: TypeStoreAvailabilityFragment[];
};

export const ProductDetailAvailability: FC<ProductDetailContentProps> = ({
    isSellingDenied,
    availability,
    availableStoresCount,
    isInquiryType,
    storeAvailabilities,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    if (isSellingDenied) {
        return null;
    }

    return (
        <ProductAvailability
            availability={availability}
            availableStoresCount={availableStoresCount}
            isInquiryType={isInquiryType}
            className={twJoin(
                'font-secondary mr-1 flex items-center',
                availability.status === TypeAvailabilityStatusEnum.InStock && 'cursor-pointer hover:underline',
            )}
            onClick={() =>
                availability.status === TypeAvailabilityStatusEnum.InStock &&
                updatePortalContent(
                    <Popup contentClassName="overflow-auto" title={t('Availability in stores')}>
                        <ProductDetailAvailabilityList storeAvailabilities={storeAvailabilities} />
                    </Popup>,
                )
            }
        />
    );
};
