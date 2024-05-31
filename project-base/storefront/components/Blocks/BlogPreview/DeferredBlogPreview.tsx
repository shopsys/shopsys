import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import { useBlogArticlesQuery } from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { useBlogUrlQuery } from 'graphql/requests/blogCategories/queries/BlogUrlQuery.generated';
import dynamic from 'next/dynamic';
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
    const [{ data: blogUrlData, fetching: isBlogUrlFetching }] = useBlogUrlQuery();
    const blogUrl = blogUrlData?.blogCategories[0].link;

    const shouldRender = useDeferredRender('blog_preview');

    return shouldRender ? (
        <BlogPreview
            blogArticles={blogPreviewData?.blogArticles.edges}
            blogUrl={blogUrl}
            fetchingArticles={areBlogArticlesFetching}
            fetchingBlogUrl={isBlogUrlFetching}
        />
    ) : (
        <BlogPreviewPlaceholder blogArticles={blogPreviewData?.blogArticles.edges} blogUrl={blogUrl} />
    );
};
