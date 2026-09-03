import { ProductReviewsActions } from 'components/Blocks/ProductReviews/ProductReviewsActions';
import { ProductReviewsEmptyState } from 'components/Blocks/ProductReviews/ProductReviewsEmptyState';
import { ProductReviewsHeader } from 'components/Blocks/ProductReviews/ProductReviewsHeader';
import { ProductReviewsList } from 'components/Blocks/ProductReviews/ProductReviewsList';
import { ProductReviewsSorting } from 'components/Blocks/ProductReviews/ProductReviewsSorting';
import { ReviewedProductVariantType } from 'components/Blocks/ProductReviews/productReviewTypes';
import { getProductReviewHtmlId } from 'components/Blocks/ProductReviews/productReviewUtils';
import { useCurrentCustomerUserProductFamilyReviews } from 'components/Blocks/ProductReviews/useCurrentCustomerUserProductFamilyReviews';
import { useOpenCreateProductReviewPopup } from 'components/Blocks/ProductReviews/useOpenCreateProductReviewPopup';
import { useProductReviewPolicyArticleUrl } from 'components/Blocks/ProductReviews/useProductReviewPolicyArticleUrl';
import {
    PRODUCT_REVIEWS_LOAD_MORE_PAGE_SIZE,
    useProductReviewsData,
} from 'components/Blocks/ProductReviews/useProductReviewsData';
import { SkeletonModuleProductReviews } from 'components/Blocks/Skeleton/SkeletonModuleProductReviews';
import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeProductReviewConnectionFragment } from 'graphql/requests/productReviews/fragments/ProductReviewConnectionFragment.generated';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { clamp } from 'utils/numbers/clamp';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export type ProductReviewsContentProps = {
    initialProductReviews?: TypeProductReviewConnectionFragment | null;
    productUuid: string;
    productName: string;
    variants?: ReviewedProductVariantType[];
};

export const ProductReviewsContent: FC<ProductReviewsContentProps> = ({
    initialProductReviews,
    productUuid,
    productName,
    variants,
}) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerMyReviewsUrl] = getInternationalizedStaticUrls(['/customer/my-reviews'], url);
    const productReviewPolicyArticleUrl = useProductReviewPolicyArticleUrl();
    const openCreateProductReviewPopup = useOpenCreateProductReviewPopup();

    const {
        activeOrderingMode,
        areProductReviewsFetching,
        hasMoreReviews,
        isLoadingMoreReviews,
        loadMoreReviews,
        reviews,
        setOrderingMode,
        summary,
        totalCount,
    } = useProductReviewsData(productUuid, initialProductReviews ?? undefined);
    const {
        isLoading: isWriteReviewAvailabilityLoading,
        pendingOwnReviews,
        reviewedProductReviewUuid,
        reviewedProductUuids,
    } = useCurrentCustomerUserProductFamilyReviews(productUuid);

    if (areProductReviewsFetching) {
        return <SkeletonModuleProductReviews />;
    }

    const isVariantFamily = !!variants?.length;

    // a logged in customer cannot review the same product twice, so offer only what is left to review
    const unreviewedVariants = variants?.filter((variant) => !reviewedProductUuids.has(variant.uuid));
    const canWriteReview =
        !isWriteReviewAvailabilityLoading &&
        (variants?.length
            ? unreviewedVariants !== undefined && unreviewedVariants.length > 0
            : !reviewedProductUuids.has(productUuid));
    const hasAlreadyReviewed = !isWriteReviewAvailabilityLoading && reviewedProductUuids.size > 0 && !canWriteReview;
    const reviewedProductReviewUrl = reviewedProductReviewUuid
        ? `${customerMyReviewsUrl}#${getProductReviewHtmlId(reviewedProductReviewUuid)}`
        : null;

    const handleWriteReview = () => {
        void openCreateProductReviewPopup({
            productName,
            productUuid: unreviewedVariants?.length ? null : productUuid,
            variants: unreviewedVariants,
        });
    };

    const remainingCount = clamp(totalCount - reviews.length, 0, PRODUCT_REVIEWS_LOAD_MORE_PAGE_SIZE);
    const hasReviewActions =
        isWriteReviewAvailabilityLoading ||
        canWriteReview ||
        hasAlreadyReviewed ||
        productReviewPolicyArticleUrl !== null;

    if (totalCount === 0 && pendingOwnReviews.length === 0) {
        return (
            <ProductReviewsEmptyState canWriteReview={canWriteReview}>
                {hasReviewActions && (
                    <ProductReviewsActions
                        canWriteReview={canWriteReview}
                        hasAlreadyReviewed={hasAlreadyReviewed}
                        isLoading={isWriteReviewAvailabilityLoading}
                        reviewedProductReviewUrl={reviewedProductReviewUrl}
                        policyArticleUrl={productReviewPolicyArticleUrl}
                        onWriteReview={handleWriteReview}
                    />
                )}
            </ProductReviewsEmptyState>
        );
    }

    return (
        <div className="flex flex-col gap-4">
            <ProductReviewsHeader summary={summary}>
                {hasReviewActions && (
                    <ProductReviewsActions
                        canWriteReview={canWriteReview}
                        hasAlreadyReviewed={hasAlreadyReviewed}
                        isLoading={isWriteReviewAvailabilityLoading}
                        reviewedProductReviewUrl={reviewedProductReviewUrl}
                        policyArticleUrl={productReviewPolicyArticleUrl}
                        onWriteReview={handleWriteReview}
                    />
                )}
            </ProductReviewsHeader>

            {totalCount > 0 && (
                <ProductReviewsSorting activeOrderingMode={activeOrderingMode} onChangeOrderingMode={setOrderingMode} />
            )}

            <ProductReviewsList
                isProductNameShown={isVariantFamily}
                pendingReviews={pendingOwnReviews}
                reviews={reviews}
            />

            {isLoadingMoreReviews &&
                createEmptyArray(3).map((_, index) => <SkeletonModuleProductReviews key={index} />)}

            {hasMoreReviews && (
                <Button
                    className="self-center"
                    disabled={isLoadingMoreReviews}
                    hasDisabledLook={isLoadingMoreReviews}
                    variant="secondary"
                    onClick={loadMoreReviews}
                >
                    {t('Show {{ count }} more reviews', { count: remainingCount })}
                </Button>
            )}
        </div>
    );
};
