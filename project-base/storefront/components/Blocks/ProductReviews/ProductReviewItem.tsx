import { ExtendedNextLink, type ExtendedNextLinkProps } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { WriteIcon } from 'components/Basic/Icon/WriteIcon';
import { ProductReviewPhotos } from 'components/Blocks/ProductReviews/ProductReviewPhotos';
import { ProductReviewStatus } from 'components/Blocks/ProductReviews/ProductReviewStatus';
import { ProductReviewDisplayStatus } from 'components/Blocks/ProductReviews/productReviewTypes';
import { getReviewerInitial } from 'components/Blocks/ProductReviews/productReviewUtils';
import { ReviewStars } from 'components/Blocks/ProductReviews/ReviewStars';
import { ReviewStatus } from 'components/Blocks/ProductReviews/ReviewStatus';
import { TypeProductReviewFragment } from 'graphql/requests/productReviews/fragments/ProductReviewFragment.generated';
import { TypeProductReviewStatusEnum } from 'graphql/types';
import { type ReactNode } from 'react';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type ProductReviewItemProps = {
    arePhotosOnGreyBackground?: boolean;
    id?: string;
    leadingContent?: ReactNode;
    productReview: Pick<
        TypeProductReviewFragment,
        'reviewerName' | 'productName' | 'rating' | 'text' | 'createdAt' | 'images'
    > &
        Partial<Pick<TypeProductReviewFragment, 'responseText' | 'responseCreatedAt'>>;
    productName?: ReactNode;
    reviewTitle?: ReactNode;
    reviewSubjectLink?: Pick<ExtendedNextLinkProps, 'aria-label' | 'href' | 'type'>;
    reviewStatus?: TypeProductReviewStatusEnum;
    status?: ProductReviewDisplayStatus;
};

export const ProductReviewItem: FC<ProductReviewItemProps> = ({
    arePhotosOnGreyBackground,
    children,
    className,
    id,
    leadingContent,
    productName,
    productReview,
    reviewTitle,
    reviewSubjectLink,
    reviewStatus,
    status,
}) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();

    const reviewerInitial = getReviewerInitial(productReview.reviewerName);
    const reviewerDisplayName = productReview.reviewerName ?? t('Anonymous customer');

    return (
        <li
            className={twMergeCustom(
                'flex flex-col gap-2 border-b border-b-border-less py-5 last:border-b-0',
                className,
            )}
            id={id}
        >
            <div className="grid grid-cols-[auto_minmax(0,1fr)_auto] items-start gap-x-3">
                {reviewSubjectLink ? (
                    <ExtendedNextLink
                        {...reviewSubjectLink}
                        preventRedirectOnTextSelection
                        className="group/product-link contents text-text-default no-underline hover:text-text-default hover:no-underline focus-visible:outline-hidden"
                        data-focus-color="preserve"
                    >
                        <div className="row-span-2">{leadingContent}</div>

                        <span className="w-fit min-w-0 font-semibold group-hover/product-link:text-link-hovered group-hover/product-link:underline">
                            <span className="-mx-1 rounded-sm box-decoration-clone px-1 group-focus-visible/product-link:bg-orange-500 group-focus-visible/product-link:text-text-default!">
                                {reviewTitle}
                            </span>
                        </span>
                    </ExtendedNextLink>
                ) : (
                    <>
                        <div className="row-span-2">
                            {leadingContent ?? (
                                <span
                                    aria-hidden="true"
                                    className="mt-1 flex size-10 shrink-0 items-center justify-center rounded-full bg-background-most font-semibold"
                                >
                                    {reviewerInitial ?? <UserIcon className="size-5" />}
                                </span>
                            )}
                        </div>

                        <span className="min-w-0 font-semibold">{reviewTitle ?? reviewerDisplayName}</span>
                    </>
                )}

                {reviewStatus && (
                    <div className="col-start-3 row-start-1 ml-auto shrink-0">
                        <ReviewStatus status={reviewStatus} />
                    </div>
                )}

                <div className="col-start-2 col-end-4 row-start-2 flex min-w-0 flex-col gap-3">
                    <div className="flex flex-wrap items-center gap-2">
                        <ReviewStars
                            ariaLabel={t('Rated {{ rating }} out of 5 stars', {
                                ns: 'accessibility',
                                rating: productReview.rating,
                            })}
                            rating={productReview.rating}
                        />

                        <span className="text-text-less text-xs">{formatDate(productReview.createdAt)}</span>

                        {productName && (
                            <span className="text-text-less text-xs">
                                {t('variant')} {productName}
                            </span>
                        )}

                        {status && <ProductReviewStatus status={status} />}
                    </div>

                    {productReview.text && <p className="m-0 whitespace-pre-line">{productReview.text}</p>}

                    <ProductReviewPhotos
                        isOnGreyBackground={arePhotosOnGreyBackground}
                        galleryName={productReview.productName}
                        images={productReview.images}
                    />

                    {productReview.responseText && (
                        <div className="flex flex-col gap-1 rounded-md bg-background-more p-4">
                            <div className="flex flex-wrap items-center gap-2 font-semibold text-sm">
                                <div className="flex items-center gap-1">
                                    <WriteIcon aria-hidden className="size-4" focusable="false" />
                                    {t('Shop response')}
                                </div>

                                {productReview.responseCreatedAt && (
                                    <span className="font-normal text-text-less text-xs">
                                        {formatDate(productReview.responseCreatedAt)}
                                    </span>
                                )}
                            </div>

                            <p className="m-0 whitespace-pre-line text-sm">{productReview.responseText}</p>
                        </div>
                    )}

                    {children}
                </div>
            </div>
        </li>
    );
};
