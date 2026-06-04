import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { TypeSimpleStoreAvailabilityFragment } from 'graphql/requests/storeAvailabilities/fragments/SimpleStoreAvailabilityFragment.generated';
import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';

const ProductDetailAvailabilityPopup = dynamic(
    () => import('./ProductDetailAvailabilityPopup').then((component) => component.ProductDetailAvailabilityPopup),
    { ssr: false },
);

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
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const storeCurrentFocus = useSessionStore((s) => s.storeCurrentFocus);

    if (isSellingDenied) {
        return null;
    }

    const openAvailabilityInStoresPopup = () => {
        if (availability.status !== TypeAvailabilityStatusEnum.InStock) {
            return;
        }

        storeCurrentFocus();
        updatePortalContent(<ProductDetailAvailabilityPopup storeAvailabilities={storeAvailabilities} />);
    };

    return (
        <ProductAvailability
            availability={availability}
            availableStoresCount={availableStoresCount}
            isInquiryType={isInquiryType}
            className={twJoin(
                'mr-1 flex items-center font-secondary',
                availability.status === TypeAvailabilityStatusEnum.InStock && 'cursor-pointer hover:underline',
            )}
            onClick={openAvailabilityInStoresPopup}
        />
    );
};
