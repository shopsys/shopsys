import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { ProductDetailContent } from 'components/Pages/ProductDetail/ProductDetailContent';
import {
    ProductDetailQueryApi,
    ProductDetailQueryDocumentApi,
    ProductDetailQueryVariablesApi,
    useProductDetailQueryApi,
} from 'graphql/generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { handleServerSideErrorResponseForFriendlyUrls } from 'helpers/errors/handleServerSideErrorResponseForFriendlyUrls';
import { isRedirectedFromSsr } from 'helpers/isRedirectedFromSsr';
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

    const product = productData?.product;

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
            {product && <ProductDetailContent fetching={fetching} product={product} />}

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
