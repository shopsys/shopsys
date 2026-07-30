import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import { useBlogArticlesQuery } from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import dynamic from 'next/dynamic';
import { twJoin } from 'tailwind-merge';
import { useDeferredRender } from 'utils/useDeferredRender';

const BlogPreview = dynamic(() => import('./BlogPreview').then((component) => component.BlogPreview), {
    ssr: false,
});

const BlogPreviewPlaceholder = dynamic(() =>
    import('./BlogPreviewPlaceholder').then((component) => component.BlogPreviewPlaceholder),
);

export const DeferredBlogPreview: FC = () => {
    const [{ data: blogPreviewData, fetching: areBlogArticlesFetching }] = useBlogArticlesQuery({
        variables: BLOG_PREVIEW_VARIABLES,
    });
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });
    const blogData = settingsData?.settings?.mainBlogCategoryData;

    const shouldRender = useDeferredRender('blog_preview');

    if (!blogPreviewData?.blogArticles.edges?.length) {
        return null;
    }

    const bgImageTwClass = twJoin(
        'relative bg-background-brand bg-center bg-cover py-16',
        "after:absolute after:inset-0 after:block after:bg-background-brand/80 after:content-['']",
    );

    return (
        <div
            className={bgImageTwClass}
            style={
                blogData?.mainBlogCategoryMainImage?.url
                    ? { backgroundImage: `url(${blogData.mainBlogCategoryMainImage.url})` }
                    : {}
            }
        >
            {shouldRender ? (
                <BlogPreview
                    blogArticles={blogPreviewData.blogArticles.edges}
                    blogName={blogData?.mainBlogCategoryName}
                    blogUrl={blogData?.mainBlogCategoryUrl}
                    fetchingArticles={areBlogArticlesFetching}
                />
            ) : (
                <BlogPreviewPlaceholder
                    blogArticles={blogPreviewData.blogArticles.edges}
                    blogName={blogData?.mainBlogCategoryName}
                    blogUrl={blogData?.mainBlogCategoryUrl}
                />
            )}
        </div>
    );
};
