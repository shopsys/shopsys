import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { ProductDetailContent } from 'components/Pages/ProductDetail/ProductDetailContent';
import { ProductDetailMainVariantContent } from 'components/Pages/ProductDetail/ProductDetailMainVariantContent';
import {
    useProductDetailQuery,
    TypeProductDetailQuery,
    TypeProductDetailQueryVariables,
    ProductDetailQueryDocument,
} from 'graphql/requests/products/queries/ProductDetailQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { isRedirectedFromSsr } from 'utils/isRedirectedFromSsr';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';

const ProductDetailPage: NextPage<ServerSidePropsType> = () => {
    const router = useRouter();
    const [{ data: productData, fetching }] = useProductDetailQuery({
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
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const client = createClient({
                t,
                ssrExchange,
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                redisClient,
                context,
            });

            if (isRedirectedFromSsr(context.req.headers)) {
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
            }

            const initServerSideData = await initServerSideProps({
                context,
                client,
                ssrExchange,
                domainConfig,
            });

            return initServerSideData;
        },
);

export default ProductDetailPage;
