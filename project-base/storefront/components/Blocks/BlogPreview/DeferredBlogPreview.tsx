import { Webline } from 'components/Layout/Webline/Webline';
import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import { useBlogArticlesQuery } from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
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
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });
    const blogUrl = settingsData?.settings?.mainBlogCategoryUrl;

    const shouldRender = useDeferredRender('blog_preview');

    if (!blogPreviewData?.blogArticles.edges?.length) {
        return null;
    }

    return (
        <Webline>
            {shouldRender ? (
                <BlogPreview
                    blogArticles={blogPreviewData.blogArticles.edges}
                    blogUrl={blogUrl}
                    fetchingArticles={areBlogArticlesFetching}
                />
            ) : (
                <BlogPreviewPlaceholder blogArticles={blogPreviewData.blogArticles.edges} blogUrl={blogUrl} />
            )}
        </Webline>
    );
};
