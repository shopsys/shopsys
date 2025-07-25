import { ProductDetailAvailabilityList } from './ProductDetailAvailabilityList';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { Popup } from 'components/Layout/Popup/Popup';
import { TypeStoreAvailabilityFragment } from 'graphql/requests/storeAvailabilities/fragments/StoreAvailabilityFragment.generated';
import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
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
