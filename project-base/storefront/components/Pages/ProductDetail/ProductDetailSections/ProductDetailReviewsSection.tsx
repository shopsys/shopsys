import {
    ProductReviewsContent,
    ProductReviewsContentProps,
} from 'components/Blocks/ProductReviews/ProductReviewsContent';
import { Webline } from 'components/Layout/Webline/Webline';
import { RefObject } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ProductDetailSectionHeading } from './ProductDetailSectionHeading';
import { PRODUCT_DETAIL_SECTIONS_IDS } from './ProductDetailSections';

type ProductDetailReviewsSectionProps = ProductReviewsContentProps & {
    sectionRef: RefObject<HTMLDivElement | null>;
};

export const ProductDetailReviewsSection: FC<ProductDetailReviewsSectionProps> = ({ sectionRef, ...contentProps }) => {
    const { t } = useTranslation();

    return (
        <div
            className="scroll-mt-fixed-header-with-navigation"
            id={PRODUCT_DETAIL_SECTIONS_IDS.reviews}
            ref={sectionRef}
        >
            <Webline width="vl">
                <ProductDetailSectionHeading>{t('Reviews')}</ProductDetailSectionHeading>

                <ProductReviewsContent key={contentProps.productUuid} {...contentProps} />
            </Webline>
        </div>
    );
};
