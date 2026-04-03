import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { Popup } from 'components/Layout/Popup/Popup';
import { TypeSimpleStoreAvailabilityFragment } from 'graphql/requests/storeAvailabilities/fragments/SimpleStoreAvailabilityFragment.generated';
import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ProductDetailAvailabilityList } from './ProductDetailAvailabilityList';

type ProductDetailContentProps = {
    isSellingDenied: boolean;
    availability: TypeAvailability;
    availableStoresCount: number | null;
    isInquiryType: boolean;
    storeAvailabilities: TypeSimpleStoreAvailabilityFragment[];
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
                'mr-1 flex items-center font-secondary',
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
