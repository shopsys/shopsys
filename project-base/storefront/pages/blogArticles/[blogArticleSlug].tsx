import { ArticleMetadata } from 'components/Basic/Head/ArticleMetadata';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { BlogArticleDetailContent } from 'components/Pages/BlogArticle/BlogArticleDetailContent';
import {
    BlogArticleDetailQueryDocument,
    TypeBlogArticleDetailQuery,
    TypeBlogArticleDetailQueryVariables,
    useBlogArticleDetailQuery,
} from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticleDetailQuery.generated';
import { BlogCategoriesDocument } from 'graphql/requests/blogCategories/queries/BlogCategoriesQuery.generated';
import { ProductsByCatnumsDocument } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { NextPage } from 'next';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { OgTypeEnum } from 'types/seo';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getIsRedirectedFromSsr } from 'utils/getIsRedirectedFromSsr';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { parseCatnums } from 'utils/parsing/grapesJsParser';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const Error404Content = dynamic(
    () => import('components/Pages/ErrorPage/Error404Content').then((m) => m.Error404Content),
    { ssr: false },
);

const BlogArticleDetailPage: NextPage<ServerSidePropsType> = () => {
    const router = useRouter();
    const [{ data: blogArticleData, fetching: isBlogArticleFetching }] = useBlogArticleDetailQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const blogArticleImageUrl = blogArticleData?.blogArticle?.mainImage?.url;

    const pageViewEvent = useGtmFriendlyPageViewEvent(blogArticleData?.blogArticle);
    useGtmPageViewEvent(pageViewEvent, isBlogArticleFetching);

    if (!blogArticleData && !isBlogArticleFetching) {
        return <Error404Content />;
    }

    return (
        <CommonLayout
            breadcrumbs={blogArticleData?.blogArticle?.breadcrumb}
            breadcrumbsType="blogCategory"
            canonicalQueryParams={[]}
            description={blogArticleData?.blogArticle?.seoMetaDescription}
            hreflangLinks={blogArticleData?.blogArticle?.hreflangLinks}
            isFetchingData={isBlogArticleFetching}
            ogImageUrlDefault={blogArticleImageUrl}
            ogType={OgTypeEnum.Article}
            title={blogArticleData?.blogArticle?.seoTitle || blogArticleData?.blogArticle?.name}
        >
            {!!blogArticleData?.blogArticle && (
                <>
                    <ArticleMetadata
                        datePublished={blogArticleData.blogArticle.publishDate}
                        description={blogArticleData.blogArticle.seoMetaDescription}
                        headline={blogArticleData.blogArticle.seoTitle || blogArticleData.blogArticle.name}
                        imageUrl={blogArticleData.blogArticle.mainImage?.url}
                    />
                    <BlogArticleDetailContent blogArticle={blogArticleData.blogArticle} />
                </>
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
                domainConfig,
                redisClient,
                context,
            });

            const blogArticleResponse: OperationResult<
                TypeBlogArticleDetailQuery,
                TypeBlogArticleDetailQueryVariables
            > = await client
                ?.query(BlogArticleDetailQueryDocument, {
                    urlSlug: getSlugFromServerSideUrl(context.req.url ?? '', context.req.headers),
                })
                .toPromise();

            const parsedCatnums = parseCatnums(blogArticleResponse.data?.blogArticle?.text ?? '');

            await client
                ?.query(ProductsByCatnumsDocument, {
                    catnums: parsedCatnums,
                })
                .toPromise();

            if (getIsRedirectedFromSsr(context.req.headers)) {
                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    blogArticleResponse.error,
                    blogArticleResponse.data?.blogArticle,
                    context,
                    domainConfig.url,
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
                prefetchedQueries: [{ query: BlogCategoriesDocument }],
            });

            return initServerSideData;
        },
);

export default BlogArticleDetailPage;
