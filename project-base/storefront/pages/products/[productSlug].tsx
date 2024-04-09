import {
    ProductDetailQueryDocument,
    TypeProductDetailQuery,
    TypeProductDetailQueryVariables,
} from 'graphql/requests/products/queries/ProductDetailQuery.generated';
import {
    TypeRecommendedProductsQueryVariables,
    RecommendedProductsQueryDocument,
} from 'graphql/requests/products/queries/RecommendedProductsQuery.generated';
import { TypeRecommendationType } from 'graphql/types';
import { NextPage } from 'next';
import dynamic from 'next/dynamic';
import { Suspense } from 'react';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';

const ProductDetailWrapper = dynamic(() =>
    import('components/Pages/ProductDetail/ProductDetailWrapper').then((component) => component.ProductDetailWrapper),
);

const ProductDetailPage: NextPage<ServerSidePropsType> = () => {
    return (
        <Suspense>
            <ProductDetailWrapper />
        </Suspense>
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
