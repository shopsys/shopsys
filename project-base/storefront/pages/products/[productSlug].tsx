import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { ProductDetailContent } from 'components/Pages/ProductDetail/ProductDetailContent';
import { ProductDetailMainVariantContent } from 'components/Pages/ProductDetail/ProductDetailMainVariantContent';
import {
    ProductDetailQueryApi,
    ProductDetailQueryDocumentApi,
    ProductDetailQueryVariablesApi,
    RecommendationTypeApi,
    RecommendedProductsQueryDocumentApi,
    RecommendedProductsQueryVariablesApi,
    useProductDetailQueryApi,
} from 'graphql/generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { handleServerSideErrorResponseForFriendlyUrls } from 'helpers/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'helpers/parsing/urlParsing';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';

const ProductDetailPage: NextPage<ServerSidePropsType> = () => {
    const router = useRouter();
    const [{ data: productData, fetching }] = useProductDetailQueryApi({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const product =
        productData?.product?.__typename === 'RegularProduct' || productData?.product?.__typename === 'MainVariant'
            ? productData.product
            : null;

    const pageViewEvent = useGtmFriendlyPageViewEvent(product);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout
            breadcrumbs={product?.breadcrumb}
            breadcrumbsType="category"
            canonicalQueryParams={[]}
            description={product?.seoMetaDescription}
            hreflangLinks={product?.hreflangLinks}
            isFetchingData={fetching}
            title={product?.seoTitle || product?.name}
        >
            {product?.__typename === 'RegularProduct' && <ProductDetailContent fetching={fetching} product={product} />}

            {product?.__typename === 'MainVariant' && (
                <ProductDetailMainVariantContent fetching={fetching} product={product} />
            )}

            <LastVisitedProducts currentProductCatnum={product?.catalogNumber} />
        </CommonLayout>
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

            const productResponse: OperationResult<ProductDetailQueryApi, ProductDetailQueryVariablesApi> =
                await client!
                    .query(ProductDetailQueryDocumentApi, {
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

            const initServerSideData = await initServerSideProps<RecommendedProductsQueryVariablesApi>({
                context,
                client,
                ssrExchange,
                domainConfig,
                prefetchedQueries: [
                    ...(domainConfig.isLuigisBoxActive && productResponse.data?.product?.__typename === 'RegularProduct'
                        ? [
                              {
                                  query: RecommendedProductsQueryDocumentApi,
                                  variables: {
                                      itemUuids: [productResponse.data.product.uuid],
                                      userIdentifier: cookiesStoreState.userIdentifier,
                                      recommendationType: RecommendationTypeApi.ItemDetailApi,
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
