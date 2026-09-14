import { ArticleMetadata } from 'components/Basic/Head/ArticleMetadata';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { BlogArticleDetailContent } from 'components/Pages/BlogArticle/BlogArticleDetailContent';
import {
    BlogArticleDetailQueryDocument,
    useBlogArticleDetailQuery,
} from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticleDetailQuery.generated';
import { BlogCategoriesDocument } from 'graphql/requests/blogCategories/queries/BlogCategoriesQuery.generated';
import { ProductsByCatnumsDocument } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { useGtmFriendlyPageReadyEvent } from 'gtm/factories/useGtmFriendlyPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { NextPage } from 'next';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { OgTypeEnum } from 'types/seo';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { parseCatnums } from 'utils/parsing/grapesJsParser';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { buildServerSideProps, prefetchLayoutQueries, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

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

    const pageReadyEvent = useGtmFriendlyPageReadyEvent(blogArticleData?.blogArticle);
    useGtmPageReadyEvent(pageReadyEvent, isBlogArticleFetching);

    if (!blogArticleData && !isBlogArticleFetching) {
        return <Error404Content />;
    }

    const isDraft = blogArticleData?.blogArticle?.status === 'draft';
    const isPreview = blogArticleData?.blogArticle?.status === 'preview';
    const isFuturePublishDate =
        !!blogArticleData?.blogArticle?.publishDate && new Date(blogArticleData.blogArticle.publishDate) > new Date();
    const shouldNoIndex = isDraft || isPreview || isFuturePublishDate;

    return (
        <CommonLayout
            breadcrumbs={blogArticleData?.blogArticle?.breadcrumb}
            breadcrumbsType="blogCategory"
            canonicalQueryParams={[]}
            defaultMetaRobots={shouldNoIndex ? 'noindex, nofollow' : undefined}
            description={blogArticleData?.blogArticle?.seo.metaDescription}
            hreflangLinks={blogArticleData?.blogArticle?.hreflangLinks}
            isFetchingData={isBlogArticleFetching}
            ogImageUrlDefault={blogArticleImageUrl}
            ogType={OgTypeEnum.Article}
            seo={blogArticleData?.blogArticle?.seo}
            title={blogArticleData?.blogArticle?.seo.title || blogArticleData?.blogArticle?.name}
        >
            {!!blogArticleData?.blogArticle && (
                <>
                    <ArticleMetadata
                        authorName={blogArticleData.blogArticle.author?.name}
                        datePublished={blogArticleData.blogArticle.publishDate}
                        description={blogArticleData.blogArticle.seo.metaDescription}
                        headline={blogArticleData.blogArticle.seo.h1 || blogArticleData.blogArticle.name}
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

            const blogArticlePromise = client
                .query(BlogArticleDetailQueryDocument, {
                    urlSlug: getSlugFromServerSideUrl(context.req.url ?? '', context.req.headers),
                })
                .toPromise();

            const [blogArticleResponse, layoutResult] = await Promise.all([
                blogArticlePromise,
                prefetchLayoutQueries({
                    client,
                    context,
                    domainConfig,
                    prefetchedQueries: [{ query: BlogCategoriesDocument }],
                }),
            ]);

            const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                blogArticleResponse.error,
                blogArticleResponse.data?.blogArticle,
                context,
                domainConfig.url,
            );

            if (serverSideErrorResponse) {
                return serverSideErrorResponse;
            }

            const parsedCatnums = parseCatnums(blogArticleResponse.data?.blogArticle?.text ?? '');
            if (parsedCatnums.length > 0) {
                await client.query(ProductsByCatnumsDocument, { catnums: parsedCatnums }).toPromise();
            }

            return buildServerSideProps({
                layoutResult,
                client,
                redisClient,
                ssrExchange,
                context,
                domainConfig,
                pageQueryResults: [blogArticleResponse],
            });
        },
);

export default BlogArticleDetailPage;
