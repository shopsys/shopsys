import type { ReviewedProductVariantType } from 'components/Blocks/ProductReviews/productReviewTypes';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.generated';
import { TypeParameterFragment } from 'graphql/requests/parameters/fragments/ParameterFragment.generated';
import { TypeProductReviewConnectionFragment } from 'graphql/requests/productReviews/fragments/ProductReviewConnectionFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useHashNavigation } from 'utils/ui/useHashNavigation';
import { ProductDetailFilesSection } from './ProductDetailFilesSection';
import { ProductDetailOverviewSection } from './ProductDetailOverviewSection';
import { ProductDetailParametersSection } from './ProductDetailParametersSection';
import { ProductDetailRelatedProductsSection } from './ProductDetailRelatedProductsSection';
import { ProductDetailReviewsSection } from './ProductDetailReviewsSection';
import { ProductDetailSectionNavigation } from './ProductDetailSectionNavigation';

type ProductDetailSectionsProps = {
    description: string | null;
    parameters: TypeParameterFragment[];
    relatedProducts: TypeListedProductFragment[];
    files: TypeFileFragment[];
    product?: TypeProductDetailFragment;
    initialProductReviews?: TypeProductReviewConnectionFragment | null;
    productUuid: string;
    productFullName: string;
    productVariants?: ReviewedProductVariantType[];
    reviewsTotalCount?: number;
};

export const PRODUCT_DETAIL_SECTIONS_IDS = {
    overview: 'overview',
    parameters: 'parameters',
    reviews: 'reviews',
    files: 'files',
    relatedProducts: 'related-products',
} as const;

export const ProductDetailSections: FC<ProductDetailSectionsProps> = ({
    description,
    parameters,
    relatedProducts,
    files,
    product,
    initialProductReviews,
    productUuid,
    productFullName,
    productVariants,
    reviewsTotalCount,
}) => {
    const { t } = useTranslation();
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });
    const areProductReviewsEnabled = settingsData?.settings?.productReviewsEnabled === true;

    const overviewRef = useRef<HTMLDivElement>(null);
    const parametersRef = useRef<HTMLDivElement>(null);
    const stickyActionBoundaryRef = useRef<HTMLDivElement>(null);
    const reviewsRef = useRef<HTMLDivElement>(null);
    const filesRef = useRef<HTMLDivElement>(null);
    const relatedProductsRef = useRef<HTMLDivElement>(null);

    // `.filter()` only reads `isVisible`, not `ref.current`.
    const sections = [
        { id: PRODUCT_DETAIL_SECTIONS_IDS.overview, label: t('Overview'), ref: overviewRef, isVisible: true },
        {
            id: PRODUCT_DETAIL_SECTIONS_IDS.parameters,
            label: t('Parameters'),
            ref: parametersRef,
            isVisible: !!parameters.length,
        },
        {
            id: PRODUCT_DETAIL_SECTIONS_IDS.reviews,
            label: reviewsTotalCount ? `${t('Reviews')} (${reviewsTotalCount})` : t('Reviews'),
            ref: reviewsRef,
            isVisible: areProductReviewsEnabled,
        },
        { id: PRODUCT_DETAIL_SECTIONS_IDS.files, label: t('Files'), ref: filesRef, isVisible: !!files.length },
        {
            id: PRODUCT_DETAIL_SECTIONS_IDS.relatedProducts,
            label: t('Related Products'),
            ref: relatedProductsRef,
            isVisible: !!relatedProducts.length,
        },
    ].filter((section) => section.isVisible);

    const { scrollToSection, activeSection } = useHashNavigation(sections);

    return (
        <div>
            <ProductDetailSectionNavigation
                activeSection={activeSection}
                product={product}
                sections={sections}
                stickyActionBoundaryRef={stickyActionBoundaryRef}
                onSectionClick={scrollToSection}
            />

            <VerticalStack gap="lg">
                <ProductDetailOverviewSection description={description} sectionRef={overviewRef} />

                {!!parameters.length && (
                    <ProductDetailParametersSection parameters={parameters} sectionRef={parametersRef} />
                )}

                {areProductReviewsEnabled && (
                    <ProductDetailReviewsSection
                        initialProductReviews={initialProductReviews}
                        productName={productFullName}
                        productUuid={productUuid}
                        sectionRef={reviewsRef}
                        variants={productVariants}
                    />
                )}

                {!!files.length && <ProductDetailFilesSection files={files} sectionRef={filesRef} />}

                {!!relatedProducts.length && (
                    <ProductDetailRelatedProductsSection
                        relatedProducts={relatedProducts}
                        sectionRef={relatedProductsRef}
                    />
                )}
            </VerticalStack>

            <div aria-hidden="true" className="h-px" ref={stickyActionBoundaryRef} />
        </div>
    );
};
