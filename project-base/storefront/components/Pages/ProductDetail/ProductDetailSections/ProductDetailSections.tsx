import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.generated';
import { TypeParameterFragment } from 'graphql/requests/parameters/fragments/ParameterFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useHashNavigation } from 'utils/ui/useHashNavigation';
import { ProductDetailFilesSection } from './ProductDetailFilesSection';
import { ProductDetailOverviewSection } from './ProductDetailOverviewSection';
import { ProductDetailParametersSection } from './ProductDetailParametersSection';
import { ProductDetailRelatedProductsSection } from './ProductDetailRelatedProductsSection';
import { ProductDetailSectionNavigation } from './ProductDetailSectionNavigation';

type ProductDetailSectionsProps = {
    description: string | null;
    parameters: TypeParameterFragment[];
    relatedProducts: TypeListedProductFragment[];
    files: TypeFileFragment[];
    product?: TypeProductDetailFragment;
};

export const PRODUCT_DETAIL_SECTIONS_IDS = {
    overview: 'overview',
    parameters: 'parameters',
    relatedProducts: 'related-products',
    files: 'files',
} as const;

export const ProductDetailSections: FC<ProductDetailSectionsProps> = ({
    description,
    parameters,
    relatedProducts,
    files,
    product,
}) => {
    const { t } = useTranslation();

    const overviewRef = useRef<HTMLDivElement>(null);
    const parametersRef = useRef<HTMLDivElement>(null);
    const relatedProductsRef = useRef<HTMLDivElement>(null);
    const filesRef = useRef<HTMLDivElement>(null);
    const stickyActionBoundaryRef = useRef<HTMLDivElement>(null);

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
            id: PRODUCT_DETAIL_SECTIONS_IDS.files,
            label: t('Files'),
            ref: filesRef,
            isVisible: !!files.length,
        },
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
