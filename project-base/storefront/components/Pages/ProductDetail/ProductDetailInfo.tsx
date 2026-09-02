import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductReviewsSummaryBadge } from 'components/Blocks/ProductReviews/ProductReviewsSummaryBadge';
import { TypeProductReviewsSummaryFragment } from 'graphql/requests/productReviews/fragments/ProductReviewsSummaryFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ProductDetailUsps } from './ProductDetailUsps';

type ProductDetailInfoProps = {
    brand?: {
        name: string;
        slug: string;
    } | null;
    catalogNumber: string;
    reviewsSummary?: TypeProductReviewsSummaryFragment | null;
    shortDescription?: string | null;
    usps?: string[];
};

export const ProductDetailInfo: FC<ProductDetailInfoProps> = ({
    brand,
    catalogNumber,
    reviewsSummary,
    shortDescription,
    usps,
}) => {
    const { t } = useTranslation();

    return (
        <>
            <div className="flex items-center gap-5 text-sm">
                {brand && (
                    <div>
                        <span className="text-text-less">{t('Brand')}: </span>

                        <ExtendedNextLink
                            className="font-semibold text-sm text-text-less no-underline hover:underline"
                            href={brand.slug}
                            title={t('Go to brand page')}
                            type="brand"
                            aria-label={t('Go to brand page of {{ brandName }}', {
                                ns: 'accessibility',
                                brandName: brand.name,
                            })}
                        >
                            {brand.name}
                        </ExtendedNextLink>
                    </div>
                )}

                <span className="text-text-less">
                    {t('Code')}: {catalogNumber}
                </span>
            </div>

            <ProductReviewsSummaryBadge reviewsSummary={reviewsSummary ?? null} />

            {shortDescription && <div className="text-sm">{shortDescription}</div>}

            {usps && !!usps.length && <ProductDetailUsps usps={usps} />}
        </>
    );
};
