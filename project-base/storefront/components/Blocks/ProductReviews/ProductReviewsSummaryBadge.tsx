import { formatAverageRating } from 'components/Blocks/ProductReviews/productReviewUtils';
import { ReviewStars } from 'components/Blocks/ProductReviews/ReviewStars';
import { PRODUCT_DETAIL_SECTIONS_IDS } from 'components/Pages/ProductDetail/ProductDetailSections/ProductDetailSections';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeProductReviewsSummaryFragment } from 'graphql/requests/productReviews/fragments/ProductReviewsSummaryFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductReviewsSummaryBadgeProps = {
    reviewsSummary: TypeProductReviewsSummaryFragment | null;
};

export const ProductReviewsSummaryBadge: FC<ProductReviewsSummaryBadgeProps> = ({ reviewsSummary }) => {
    const { t } = useTranslation();
    const { defaultLocale } = useDomainConfig();

    if (!reviewsSummary || reviewsSummary.totalCount === 0 || reviewsSummary.averageRating === null) {
        return null;
    }

    return (
        <div className="flex items-center gap-2 self-start text-sm">
            <ReviewStars rating={reviewsSummary.averageRating} />

            <span className="font-semibold text-text-default">
                {formatAverageRating(reviewsSummary.averageRating, defaultLocale)}
            </span>

            <a
                className="text-link-default text-sm no-underline hover:text-link-hovered hover:underline"
                href={`#${PRODUCT_DETAIL_SECTIONS_IDS.reviews}`}
            >
                {t('{{ count }} reviews', { count: reviewsSummary.totalCount })}
            </a>
        </div>
    );
};
