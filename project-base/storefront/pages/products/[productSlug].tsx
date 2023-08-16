import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { ProductDetailContent } from 'components/Pages/ProductDetail/ProductDetailContent';
import { ProductDetailMainVariantContent } from 'components/Pages/ProductDetail/ProductDetailMainVariantContent';
import { ProductDetailPageSkeleton } from 'components/Pages/ProductDetail/ProductDetailPageSkeleton';
import {
    ProductDetailQueryApi,
    ProductDetailQueryDocumentApi,
    ProductDetailQueryVariablesApi,
    useProductDetailQueryApi,
} from 'graphql/generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/helpers/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/isServer';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'helpers/parsing/urlParsing';
import { createClient } from 'urql/createClient';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';

const ProductDetailPage: NextPage = () => {
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
        <CommonLayout title={product?.seoTitle || product?.name} description={product?.seoMetaDescription}>
            {!!product?.breadcrumb && (
                <Webline>
                    <Breadcrumbs type="category" key="breadcrumb" breadcrumb={product.breadcrumb} />
                </Webline>
            )}
            {fetching && <ProductDetailPageSkeleton />}

            {product?.__typename === 'RegularProduct' && <ProductDetailContent product={product} fetching={fetching} />}

            {product?.__typename === 'MainVariant' && (
                <ProductDetailMainVariantContent product={product} fetching={fetching} />
            )}
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
                const productResponse: OperationResult<ProductDetailQueryApi, ProductDetailQueryVariablesApi> =
                    await client!
                        .query(ProductDetailQueryDocumentApi, {
                            urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                        })
                        .toPromise();

                if ((!productResponse.data || !productResponse.data.product) && !(context.res.statusCode === 503)) {
                    return {
                        notFound: true,
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
