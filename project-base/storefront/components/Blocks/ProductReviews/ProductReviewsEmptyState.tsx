import { StarIcon } from 'components/Basic/Icon/StarIcon';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductReviewsEmptyStateProps = {
    canWriteReview: boolean;
};

export const ProductReviewsEmptyState: FC<ProductReviewsEmptyStateProps> = ({ canWriteReview, children }) => {
    const { t } = useTranslation();

    return (
        <div className="flex flex-col items-center gap-3 py-3 text-center">
            <div className="flex size-12 shrink-0 items-center justify-center rounded-full bg-background-most text-icon-default">
                <StarIcon aria-hidden className="size-6" />
            </div>

            <div className="flex flex-col gap-1">
                <h3 className="h5">{t('No reviews yet')}</h3>

                {canWriteReview && (
                    <p className="text-sm text-text-less">
                        {t('Share your experience and help other customers decide.')}
                    </p>
                )}
            </div>

            {children && <div className="flex w-full flex-col-reverse items-center gap-2 sm:w-auto">{children}</div>}
        </div>
    );
};
