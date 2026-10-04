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

    const formattedAverageRating = formatAverageRating(reviewsSummary.averageRating, defaultLocale);

    return (
        <div className="flex items-center gap-2 self-start text-sm">
            <ReviewStars rating={reviewsSummary.averageRating} />

            <span aria-hidden="true" className="font-semibold text-text-default">
                {formattedAverageRating}
            </span>

            <a
                aria-label={t('Average rating {{ averageRating }} out of 5, review count {{ count }}, go to reviews', {
                    ns: 'accessibility',
                    averageRating: formattedAverageRating,
                    count: reviewsSummary.totalCount,
                })}
                className="text-link-default text-sm no-underline hover:text-link-hovered hover:underline"
                href={`#${PRODUCT_DETAIL_SECTIONS_IDS.reviews}`}
            >
                {t('{{ count }} reviews', { count: reviewsSummary.totalCount })}
            </a>
        </div>
    );
};
