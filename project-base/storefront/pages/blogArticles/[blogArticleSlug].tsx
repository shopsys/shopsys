import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { BlogArticleDetailContent } from 'components/Pages/BlogArticle/BlogArticleDetailContent';
import {
    useBlogArticleDetailQuery,
    BlogArticleDetailQuery,
    BlogArticleDetailQueryVariables,
    BlogArticleDetailQueryDocument,
} from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticleDetailQuery.generated';
import { ProductsByCatnumsDocument } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { handleServerSideErrorResponseForFriendlyUrls } from 'helpers/errors/handleServerSideErrorResponseForFriendlyUrls';
import { isRedirectedFromSsr } from 'helpers/isRedirectedFromSsr';
import { getSlugFromServerSideUrl } from 'helpers/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'helpers/parsing/getSlugFromUrl';
import { parseCatnums } from 'helpers/parsing/grapesJsParser';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';

const BlogArticleDetailPage: NextPage<ServerSidePropsType> = () => {
    const router = useRouter();
    const [{ data: blogArticleData, fetching }] = useBlogArticleDetailQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const pageViewEvent = useGtmFriendlyPageViewEvent(blogArticleData?.blogArticle);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout
            breadcrumbs={blogArticleData?.blogArticle?.breadcrumb}
            breadcrumbsType="blogCategory"
            canonicalQueryParams={[]}
            description={blogArticleData?.blogArticle?.seoMetaDescription}
            hreflangLinks={blogArticleData?.blogArticle?.hreflangLinks}
            isFetchingData={fetching}
            title={blogArticleData?.blogArticle?.seoTitle}
        >
            {!!blogArticleData?.blogArticle && <BlogArticleDetailContent blogArticle={blogArticleData.blogArticle} />}
            <LastVisitedProducts />
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
                const blogArticleResponse: OperationResult<BlogArticleDetailQuery, BlogArticleDetailQueryVariables> =
                    await client!
                        .query(BlogArticleDetailQueryDocument, {
                            urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                        })
                        .toPromise();

                const parsedCatnums = parseCatnums(blogArticleResponse.data?.blogArticle?.text ?? '');

                await client!
                    .query(ProductsByCatnumsDocument, {
                        catnums: parsedCatnums,
                    })
                    .toPromise();

                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    blogArticleResponse.error?.graphQLErrors,
                    blogArticleResponse.data?.blogArticle,
                    context.res,
                );

                if (serverSideErrorResponse) {
                    return serverSideErrorResponse;
                }
            }

            const initServerSideData = await initServerSideProps({
                context,
                client,
                domainConfig,
                ssrExchange,
            });

            return initServerSideData;
        },
);

export default BlogArticleDetailPage;
