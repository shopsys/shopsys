import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useProductReviewsQuery } from 'graphql/requests/productReviews/queries/ProductReviewsQuery.generated';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum, TypeProductReviewOrderingModeEnum } from 'graphql/types';
import Head from 'next/head';
import { useRouter } from 'next/router';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { serializeJsonForScriptTag } from 'utils/serialization/serializeJsonForScriptTag';

export const STRUCTURED_DATA_REVIEWS_COUNT = 5;

type ProductMetadataProps = {
    product: TypeProductDetailFragment | TypeMainVariantDetailFragment;
};

export const ProductMetadata: FC<ProductMetadataProps> = ({ product }) => {
    const { currencyCode } = useDomainConfig();
    const router = useRouter();
    const { t } = useTranslation();

    const reviewsSummary = product.reviewsSummary;
    const hasReviews = !!reviewsSummary && reviewsSummary.totalCount > 0;
    const [{ data: productReviewsData }] = useProductReviewsQuery({
        variables: {
            productUuid: product.uuid,
            orderingMode: TypeProductReviewOrderingModeEnum.Newest,
            first: STRUCTURED_DATA_REVIEWS_COUNT,
            after: null,
        },
        pause: !hasReviews,
    });

    const reviews = (productReviewsData?.productReviews.edges ?? [])
        .flatMap((edge) => (edge?.node ? [edge.node] : []))
        .map((productReview) => ({
            '@type': 'Review',
            author: {
                '@type': 'Person',
                name: productReview.reviewerName ?? t('Anonymous customer'),
            },
            datePublished: productReview.createdAt.slice(0, 10),
            ...(productReview.text !== null && { reviewBody: productReview.text }),
            reviewRating: {
                '@type': 'Rating',
                ratingValue: productReview.rating,
                bestRating: 5,
                worstRating: 1,
            },
        }));

    return (
        <Head>
            <script
                key="product-metadata"
                id="product-metadata"
                type="application/ld+json"
                dangerouslySetInnerHTML={{
                    __html: serializeJsonForScriptTag({
                        '@context': 'https://schema.org/',
                        '@type': 'Product',
                        name: product.fullName,
                        image: product.images.length > 0 ? product.images[0].url : null,
                        description: product.description,
                        sku: product.catalogNumber,
                        mpn: product.ean,
                        brand: {
                            '@type': 'Brand',
                            name: product.brand?.name,
                        },
                        offers: {
                            '@type': 'Offer',
                            url: router.asPath,
                            priceCurrency: currencyCode,
                            price: product.price.priceWithVat,
                            itemCondition: 'https://schema.org/NewCondition',
                            availability: getSchemaOrgAvailability(product.availability.status),
                        },
                        ...(hasReviews &&
                            reviewsSummary.averageRating !== null && {
                                aggregateRating: {
                                    '@type': 'AggregateRating',
                                    ratingValue: reviewsSummary.averageRating,
                                    reviewCount: reviewsSummary.totalCount,
                                    bestRating: 5,
                                    worstRating: 1,
                                },
                            }),
                        ...(reviews.length > 0 && { review: reviews }),
                    }),
                }}
            />
        </Head>
    );
};

const schemaOrgAvailabilityByStatus: Record<TypeAvailabilityStatusEnum, string> = {
    [TypeAvailabilityStatusEnum.InStock]: 'https://schema.org/InStock',
    [TypeAvailabilityStatusEnum.OutOfStock]: 'https://schema.org/OutOfStock',
    [TypeAvailabilityStatusEnum.ExpectedRestock]: 'https://schema.org/BackOrder',
};

const getSchemaOrgAvailability = (availabilityStatus: TypeAvailabilityStatusEnum): string =>
    schemaOrgAvailabilityByStatus[availabilityStatus];
