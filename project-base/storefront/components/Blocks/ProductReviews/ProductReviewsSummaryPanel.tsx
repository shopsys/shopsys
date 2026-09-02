import { StarIcon } from 'components/Basic/Icon/StarIcon';
import { formatAverageRating } from 'components/Blocks/ProductReviews/productReviewUtils';
import { ReviewStars } from 'components/Blocks/ProductReviews/ReviewStars';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeProductReviewsSummaryFragment } from 'graphql/requests/productReviews/fragments/ProductReviewsSummaryFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductReviewsSummaryPanelProps = {
    summary: TypeProductReviewsSummaryFragment;
};

export const ProductReviewsSummaryPanel: FC<ProductReviewsSummaryPanelProps> = ({ children, summary }) => {
    const { t } = useTranslation();
    const { defaultLocale } = useDomainConfig();

    const averageRating = summary.averageRating ?? 0;
    const formattedAverageRating = formatAverageRating(averageRating, defaultLocale);

    return (
        <div className="rounded-xl bg-background-more px-5 py-5 lg:grid lg:grid-cols-[auto_minmax(0,1fr)_auto] lg:items-center lg:gap-10 lg:px-10">
            <div className="flex flex-col gap-6 sm:flex-row sm:items-center sm:gap-10 lg:contents">
                <div className="flex flex-col items-center gap-1">
                    <span className="font-bold font-secondary text-5xl">{formattedAverageRating}</span>

                    <ReviewStars
                        starClassName="size-5"
                        ariaLabel={t('Average rating {{ averageRating }} out of 5', {
                            ns: 'accessibility',
                            averageRating: formattedAverageRating,
                        })}
                        rating={averageRating}
                    />

                    <span className="text-sm text-text-less">
                        {t('out of 5')} · {t('{{ count }} reviews', { count: summary.totalCount })}
                    </span>
                </div>

                <ul className="m-0 flex flex-1 list-none flex-col gap-1 p-0">
                    {summary.ratingCounts.map((ratingCount) => (
                        <li key={ratingCount.rating} className="flex items-center gap-3 text-sm">
                            <span className="flex w-8 shrink-0 items-center justify-end gap-1 whitespace-nowrap">
                                {ratingCount.rating}
                                <StarIcon aria-hidden="true" className="size-3.5 text-orange-500" fill="currentColor" />
                            </span>

                            <span className="h-2 flex-1 overflow-hidden rounded-full bg-background-most">
                                <span
                                    className="block h-full rounded-full bg-orange-500"
                                    style={{
                                        width:
                                            summary.totalCount > 0
                                                ? `${(ratingCount.count / summary.totalCount) * 100}%`
                                                : 0,
                                    }}
                                />
                            </span>

                            <span className="w-8 shrink-0 text-right text-text-less">{ratingCount.count}</span>
                        </li>
                    ))}
                </ul>
            </div>

            {children && (
                <div className="mt-4 flex flex-col-reverse items-center gap-3 border-border-less border-t pt-4 lg:mt-0 lg:items-stretch lg:border-t-0 lg:pt-0">
                    {children}
                </div>
            )}
        </div>
    );
};
