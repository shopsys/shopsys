import { SkeletonPageProductDetail } from 'components/Blocks/Skeleton/SkeletonPageProductDetail';
import { SkeletonPageProductDetailMainVariant } from 'components/Blocks/Skeleton/SkeletonPageProductDetailMainVariant';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PageDefer } from 'components/Layout/PageDefer';
import {
    ProductDetailQueryDocument,
    useProductDetailQuery,
} from 'graphql/requests/products/queries/ProductDetailQuery.generated';
import { RecommendedProductsQueryDocument } from 'graphql/requests/products/queries/RecommendedProductsQuery.generated';
import { TypeRecommendationType } from 'graphql/types';
import { NextPage } from 'next';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { FriendlyPagesDestinations } from 'types/friendlyUrl';
import { createClient } from 'urql/createClient';
import { getBasePathWithLocale } from 'utils/domain/domainUtils';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { getRecommenderClientIdentifier } from 'utils/recommender/getRecommenderClientIdentifier';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { buildServerSideProps, prefetchLayoutQueries, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const ProductDetailContent = dynamic(
    () =>
        import('components/Pages/ProductDetail/ProductDetailContent').then(
            (component) => component.ProductDetailContent,
        ),
    {
        loading: () => <SkeletonPageProductDetail />,
    },
);

const ProductDetailMainVariantContent = dynamic(
    () =>
        import('components/Pages/ProductDetail/ProductDetailMainVariantContent').then(
            (component) => component.ProductDetailMainVariantContent,
        ),
    {
        loading: () => <SkeletonPageProductDetailMainVariant />,
    },
);

const ProductDetailPage: NextPage<ServerSidePropsType> = () => {
    const router = useRouter();
    const [{ data: productData, fetching: isProductFetching }] = useProductDetailQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const product =
        productData?.product?.__typename === 'RegularProduct' || productData?.product?.__typename === 'MainVariant'
            ? productData.product
            : null;

    const firstImageUrl = product?.images[0]?.url;

    return (
        <PageDefer>
            <CommonLayout
                breadcrumbs={product?.breadcrumb}
                breadcrumbsType="category"
                canonicalQueryParams={[]}
                description={product?.seoMetaDescription}
                hreflangLinks={product?.hreflangLinks}
                isFetchingData={isProductFetching}
                ogImageUrlDefault={firstImageUrl}
                title={product?.seoTitle || product?.name}
            >
                {product?.__typename === 'RegularProduct' && (
                    <ProductDetailContent isProductDetailFetching={isProductFetching} product={product} />
                )}

                {product?.__typename === 'MainVariant' && (
                    <ProductDetailMainVariantContent isProductDetailFetching={isProductFetching} product={product} />
                )}
            </CommonLayout>
        </PageDefer>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t, cookiesStoreState }) =>
        async (context) => {
            const client = createClient({
                t,
                ssrExchange,
                domainConfig,
                redisClient,
                context,
            });

            const productPromise = client
                .query(ProductDetailQueryDocument, {
                    urlSlug: getSlugFromServerSideUrl(context.req.url ?? '', context.req.headers),
                })
                .toPromise();

            const [productResponse, layoutResult] = await Promise.all([
                productPromise,
                prefetchLayoutQueries({ client, context, domainConfig }),
            ]);

            const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                productResponse.error,
                productResponse.data?.product,
                context,
                domainConfig.url,
            );

            if (serverSideErrorResponse) {
                return serverSideErrorResponse;
            }

            if (
                productResponse.data?.product?.__typename === 'Variant' &&
                productResponse.data.product.mainVariant?.slug
            ) {
                return {
                    redirect: {
                        destination: getBasePathWithLocale(productResponse.data.product.mainVariant.slug, context),
                        permanent: false,
                    },
                };
            }

            const productData = productResponse.data?.product;
            if (domainConfig.isLuigisBoxActive && productData?.__typename === 'RegularProduct') {
                await client
                    .query(RecommendedProductsQueryDocument, {
                        itemUuids: [productData.uuid],
                        userIdentifier: cookiesStoreState.userIdentifier,
                        recommendationType: TypeRecommendationType.ItemDetail,
                        recommenderClientIdentifier: getRecommenderClientIdentifier(FriendlyPagesDestinations.product),
                        limit: 10,
                    })
                    .toPromise();
            }

            return buildServerSideProps({
                layoutResult,
                client,
                ssrExchange,
                context,
                domainConfig,
                pageQueryResults: [productResponse],
            });
        },
);

export default ProductDetailPage;
