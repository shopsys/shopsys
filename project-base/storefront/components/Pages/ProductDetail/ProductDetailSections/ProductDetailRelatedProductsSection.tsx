import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
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
            className="scroll-mt-fixed-header-with-navigation"
            data-tid={`${TIDs.product_detail_section_}${PRODUCT_DETAIL_SECTIONS_IDS.relatedProducts}`}
            id={PRODUCT_DETAIL_SECTIONS_IDS.relatedProducts}
            ref={sectionRef}
        >
            <Webline>
                <ProductDetailSectionHeading className="mb-3">{t('Related Products')}</ProductDetailSectionHeading>

                <ProductsSlider
                    ariaAnchorName="product-slider-related"
                    gtmProductListName={GtmProductListNameType.product_detail_related_products}
                    products={relatedProducts}
                />
            </Webline>
        </div>
    );
};
