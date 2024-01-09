import { CommonLayout } from 'components/Layout/CommonLayout';
import { BlogArticleDetailContent } from 'components/Pages/BlogArticle/BlogArticleDetailContent';
import {
    BlogArticleDetailQueryApi,
    BlogArticleDetailQueryDocumentApi,
    BlogArticleDetailQueryVariablesApi,
    ProductsByCatnumsDocumentApi,
    useBlogArticleDetailQueryApi,
} from 'graphql/generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { handleServerSideErrorResponseForFriendlyUrls } from 'helpers/errors/handleServerSideErrorResponseForFriendlyUrls';
import { isRedirectedFromSsr } from 'helpers/isRedirectedFromSsr';
import { parseCatnums } from 'helpers/parsing/grapesJsParser';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'helpers/parsing/urlParsing';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';

const BlogArticleDetailPage: NextPage = () => {
    const router = useRouter();
    const [{ data: blogArticleData, fetching }] = useBlogArticleDetailQueryApi({
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
            isFetchingData={fetching}
            title={blogArticleData?.blogArticle?.seoTitle}
        >
            {!!blogArticleData?.blogArticle && <BlogArticleDetailContent blogArticle={blogArticleData.blogArticle} />}
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
                const blogArticleResponse: OperationResult<
                    BlogArticleDetailQueryApi,
                    BlogArticleDetailQueryVariablesApi
                > = await client!
                    .query(BlogArticleDetailQueryDocumentApi, {
                        urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                    })
                    .toPromise();

                const parsedCatnums = parseCatnums(blogArticleResponse.data?.blogArticle?.text ?? '');

                await client!
                    .query(ProductsByCatnumsDocumentApi, {
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
