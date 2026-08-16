import type { ExtendedNextLinkProps } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { formatAverageRating, getProductReviewsSummary } from 'components/Blocks/ProductReviews/productReviewUtils';
import { ReviewStars } from 'components/Blocks/ProductReviews/ReviewStars';
import { PRODUCT_DETAIL_SECTIONS_IDS } from 'components/Pages/ProductDetail/ProductDetailSections/ProductDetailSections';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type ProductListReviewsSummaryLinkProps = {
    product: TypeListedProductFragment;
    allowKeyboardFocus?: boolean;
    isReviewCountWrappedOnMobile?: boolean;
    linkType?: ExtendedNextLinkProps['type'];
    productUrl?: string;
};

export const ProductListReviewsSummaryLink: FC<ProductListReviewsSummaryLinkProps> = ({
    product,
    allowKeyboardFocus = true,
    isReviewCountWrappedOnMobile = false,
    linkType,
    productUrl,
    className,
}) => {
    const { t } = useTranslation();
    const { defaultLocale } = useDomainConfig();
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });

    const areProductReviewsEnabled = settingsData?.settings?.productReviewsEnabled === true;
    const minimalAverageRating = settingsData?.settings?.productReviewMinimalAverageRatingForListing ?? null;
    const reviewsSummary = getProductReviewsSummary(product);

    if (
        !areProductReviewsEnabled ||
        !reviewsSummary ||
        reviewsSummary.totalCount === 0 ||
        reviewsSummary.averageRating === null ||
        (minimalAverageRating !== null && reviewsSummary.averageRating < minimalAverageRating)
    ) {
        return null;
    }

    const formattedAverageRating = formatAverageRating(reviewsSummary.averageRating, defaultLocale);
    const content = (
        <>
            <span className="flex items-center gap-1.5">
                <ReviewStars starClassName="size-4" rating={reviewsSummary.averageRating} />

                <span className="font-semibold text-text-default">{formattedAverageRating}</span>
            </span>

            <span className="text-text-link hover:text-text-link hover:underline">
                {t('{{ count }} reviews', { count: reviewsSummary.totalCount })}
            </span>
        </>
    );
    const rootClassName = twMergeCustom(
        'flex w-fit items-center gap-1.5 whitespace-nowrap text-xs no-underline hover:no-underline',
        isReviewCountWrappedOnMobile && 'flex-col items-start gap-0.5 sm:flex-row sm:items-center sm:gap-1.5',
        className,
    );
    const ariaLabel = t('Average rating {{ averageRating }} out of 5, go to reviews', {
        ns: 'accessibility',
        averageRating: formattedAverageRating,
    });

    return (
        <ExtendedNextLink
            preventRedirectOnTextSelection
            href={`${productUrl ?? product.slug}#${PRODUCT_DETAIL_SECTIONS_IDS.reviews}`}
            tabIndex={allowKeyboardFocus ? 0 : -1}
            tid={TIDs.blocks_product_list_reviews}
            type={linkType ?? (product.isMainVariant ? 'productMainVariant' : 'product')}
            aria-label={ariaLabel}
            className={rootClassName}
        >
            {content}
        </ExtendedNextLink>
    );
};
