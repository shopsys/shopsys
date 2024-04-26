import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { SkeletonPageProductDetail } from 'components/Blocks/Skeleton/SkeletonPageProductDetail';
import { SkeletonPageProductDetailMainVariant } from 'components/Blocks/Skeleton/SkeletonPageProductDetailMainVariant';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PageDefer } from 'components/Layout/PageDefer';
import {
    ProductDetailQueryDocument,
    TypeProductDetailQuery,
    TypeProductDetailQueryVariables,
    useProductDetailQuery,
} from 'graphql/requests/products/queries/ProductDetailQuery.generated';
import {
    TypeRecommendedProductsQueryVariables,
    RecommendedProductsQueryDocument,
} from 'graphql/requests/products/queries/RecommendedProductsQuery.generated';
import { TypeRecommendationType } from 'graphql/types';
import { NextPage } from 'next';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';

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
    const [{ data: productData, fetching }] = useProductDetailQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const product =
        productData?.product?.__typename === 'RegularProduct' || productData?.product?.__typename === 'MainVariant'
            ? productData.product
            : null;

    return (
        <PageDefer>
            <CommonLayout
                breadcrumbs={product?.breadcrumb}
                breadcrumbsType="category"
                canonicalQueryParams={[]}
                description={product?.seoMetaDescription}
                hreflangLinks={product?.hreflangLinks}
                isFetchingData={fetching}
                title={product?.seoTitle || product?.name}
            >
                {product?.__typename === 'RegularProduct' && (
                    <ProductDetailContent fetching={fetching} product={product} />
                )}

                {product?.__typename === 'MainVariant' && (
                    <ProductDetailMainVariantContent fetching={fetching} product={product} />
                )}

                <DeferredLastVisitedProducts currentProductCatnum={product?.catalogNumber} />
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
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                redisClient,
                context,
            });

            const productResponse: OperationResult<TypeProductDetailQuery, TypeProductDetailQueryVariables> =
                await client!
                    .query(ProductDetailQueryDocument, {
                        urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                    })
                    .toPromise();

            const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                productResponse.error?.graphQLErrors,
                productResponse.data?.product,
                context.res,
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
                        destination: productResponse.data.product.mainVariant.slug,
                        permanent: false,
                    },
                };
            }

            const initServerSideData = await initServerSideProps<TypeRecommendedProductsQueryVariables>({
                context,
                client,
                ssrExchange,
                domainConfig,
                prefetchedQueries: [
                    ...(domainConfig.isLuigisBoxActive && productResponse.data?.product?.__typename === 'RegularProduct'
                        ? [
                              {
                                  query: RecommendedProductsQueryDocument,
                                  variables: {
                                      itemUuids: [productResponse.data.product.uuid],
                                      userIdentifier: cookiesStoreState.userIdentifier,
                                      recommendationType: TypeRecommendationType.ItemDetail,
                                      limit: 10,
                                  },
                              },
                          ]
                        : []),
                ],
            });

            return initServerSideData;
        },
);

export default ProductDetailPage;
