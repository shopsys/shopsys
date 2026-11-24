import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';

const ProductVariantsPopup = dynamic(
    () => import('./ProductVariantsPopup').then((component) => component.ProductVariantsPopup),
    { ssr: false },
);

type ProductVariantsPopupProduct = {
    uuid: string;
    slug: string;
    fullName: string;
};

type OpenProductVariantsPopupOptions = {
    gtmProductListName: GtmProductListNameType;
    listIndex?: number;
    gtmMessageOrigin?: GtmMessageOriginType;
    onVariantAddedToComparison?: () => void;
};

export const useOpenProductVariantsPopup = () => {
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const storeCurrentFocus = useSessionStore((s) => s.storeCurrentFocus);

    return (
        product: ProductVariantsPopupProduct,
        {
            gtmProductListName,
            listIndex = 0,
            gtmMessageOrigin = GtmMessageOriginType.other,
            onVariantAddedToComparison,
        }: OpenProductVariantsPopupOptions,
    ) => {
        storeCurrentFocus();
        updatePortalContent(
            <ProductVariantsPopup
                gtmMessageOrigin={gtmMessageOrigin}
                gtmProductListName={gtmProductListName}
                listIndex={listIndex}
                productName={product.fullName}
                productSlug={product.slug}
                productUuid={product.uuid}
                onVariantAddedToComparison={onVariantAddedToComparison}
            />,
        );
    };
};
