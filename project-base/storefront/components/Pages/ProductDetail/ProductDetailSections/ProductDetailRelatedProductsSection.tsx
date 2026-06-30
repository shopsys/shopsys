import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { RefObject } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ProductDetailSectionHeading } from './ProductDetailSectionHeading';
import { PRODUCT_DETAIL_SECTIONS_IDS } from './ProductDetailSections';

type ProductDetailRelatedProductsSectionProps = {
    relatedProducts: TypeListedProductFragment[];
    sectionRef: RefObject<HTMLDivElement | null>;
};

export const ProductDetailRelatedProductsSection = ({
    relatedProducts,
    sectionRef,
}: ProductDetailRelatedProductsSectionProps) => {
    const { t } = useTranslation();

    return (
        <div
            className="scroll-mt-[calc(var(--sticky-navigation-offset,0)+5rem)]"
            id={PRODUCT_DETAIL_SECTIONS_IDS.relatedProducts}
            ref={sectionRef}
        >
            <Webline>
                <ProductDetailSectionHeading>{t('Related Products')}</ProductDetailSectionHeading>

                <ProductsSlider
                    ariaAnchorName="product-slider-related"
                    gtmProductListName={GtmProductListNameType.product_detail_related_products}
                    products={relatedProducts}
                />
            </Webline>
        </div>
    );
};
