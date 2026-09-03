import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { WriteIcon } from 'components/Basic/Icon/WriteIcon';
import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Button, getButtonIconClassName } from 'components/Forms/Button/Button';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductReviewsActionsProps = {
    canWriteReview: boolean;
    hasAlreadyReviewed: boolean;
    isLoading?: boolean;
    reviewedProductReviewUrl: string | null;
    onWriteReview: () => void;
    policyArticleUrl?: string | null;
};

export const ProductReviewsActions: FC<ProductReviewsActionsProps> = ({
    canWriteReview,
    hasAlreadyReviewed,
    isLoading = false,
    reviewedProductReviewUrl,
    onWriteReview,
    policyArticleUrl,
}) => {
    const { t } = useTranslation();

    return (
        <>
            {policyArticleUrl && (
                <Link
                    className="mx-auto gap-1 text-text-less text-xs no-underline hover:underline"
                    href={policyArticleUrl}
                    target="_blank"
                >
                    {t('How we process and verify reviews')}
                </Link>
            )}

            {isLoading && <Skeleton className="h-10 w-full rounded-button sm:w-48" />}

            {!isLoading && canWriteReview && (
                <Button aria-haspopup="dialog" className="w-full lg:w-auto" size="medium" onClick={onWriteReview}>
                    <WriteIcon aria-hidden className={getButtonIconClassName('medium')} />
                    {t('Write a review')}
                </Button>
            )}

            {!isLoading && hasAlreadyReviewed && (
                <p className="text-center text-sm text-text-less lg:text-left">
                    {reviewedProductReviewUrl ? (
                        <ExtendedNextLink
                            className={`${linkPlaceholderTwClass} text-sm`}
                            href={reviewedProductReviewUrl}
                            skeletonType="myReviews"
                        >
                            {t('You have already reviewed this product.')}
                        </ExtendedNextLink>
                    ) : (
                        t('You have already reviewed this product.')
                    )}
                </p>
            )}
        </>
    );
};
