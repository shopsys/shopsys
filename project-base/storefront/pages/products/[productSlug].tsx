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

import { useGtmFriendlyPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/misc/isServer';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { createClient } from 'helpers/urql/createClient';

import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { NextPage } from 'next';
import getT from 'next-translate/getT';
import { useRouter } from 'next/router';
import { OperationResult, ssrExchange } from 'urql';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'utils/getSlugFromUrl';

const ProductDetailPage: NextPage = () => {
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const [{ data: productData, fetching }] = useProductDetailQueryApi({
        variables: { urlSlug: getSlugFromUrl(slug) },
    });

    const product =
        productData?.product?.__typename === 'RegularProduct' || productData?.product?.__typename === 'MainVariant'
            ? productData.product
            : null;

    const pageViewEvent = useGtmFriendlyPageViewEvent(product);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout title={product?.seoTitle} description={product?.seoMetaDescription}>
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

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient, domainConfig) => async (context) => {
    const ssrCache = ssrExchange({ isClient: false });
    const t = await getT(domainConfig.defaultLocale, 'common');
    const client = createClient(t, ssrCache, domainConfig.publicGraphqlEndpoint, redisClient, context);

    if (isRedirectedFromSsr(context.req.headers)) {
        const productResponse: OperationResult<ProductDetailQueryApi, ProductDetailQueryVariablesApi> = await client!
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
        ssrCache,
        redisClient,
    });

    return initServerSideData;
});

export default ProductDetailPage;
