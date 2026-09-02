import type { ProductReviewsContentProps } from 'components/Blocks/ProductReviews/ProductReviewsContent';
import { SkeletonModuleProductReviews } from 'components/Blocks/Skeleton/SkeletonModuleProductReviews';
import { Webline } from 'components/Layout/Webline/Webline';
import dynamic from 'next/dynamic';
import { RefObject } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useDeferredRender } from 'utils/useDeferredRender';
import { ProductDetailSectionHeading } from './ProductDetailSectionHeading';
import { PRODUCT_DETAIL_SECTIONS_IDS } from './ProductDetailSections';

const ProductReviewsContent = dynamic(
    () =>
        import('components/Blocks/ProductReviews/ProductReviewsContent').then(
            (component) => component.ProductReviewsContent,
        ),
    {
        ssr: false,
        loading: () => <SkeletonModuleProductReviews />,
    },
);

type ProductDetailReviewsSectionProps = ProductReviewsContentProps & {
    sectionRef: RefObject<HTMLDivElement | null>;
};

export const ProductDetailReviewsSection: FC<ProductDetailReviewsSectionProps> = ({ sectionRef, ...contentProps }) => {
    const { t } = useTranslation();
    const shouldRender = useDeferredRender('reviews');

    return (
        <div
            className="scroll-mt-fixed-header-with-navigation"
            id={PRODUCT_DETAIL_SECTIONS_IDS.reviews}
            ref={sectionRef}
        >
            <Webline width="vl">
                <ProductDetailSectionHeading>{t('Reviews')}</ProductDetailSectionHeading>

                {shouldRender ? <ProductReviewsContent {...contentProps} /> : <SkeletonModuleProductReviews />}
            </Webline>
        </div>
    );
};
