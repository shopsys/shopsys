import { Webline } from 'components/Layout/Webline/Webline';
import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import { useBlogArticlesQuery } from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import dynamic from 'next/dynamic';
import { twJoin } from 'tailwind-merge';
import { useDeferredRender } from 'utils/useDeferredRender';

const BlogPreview = dynamic(() => import('./BlogPreview').then((component) => ({
    default: component.BlogPreview
})), {
    ssr: false,
});

const BlogPreviewPlaceholder = dynamic(() =>
    import('./BlogPreviewPlaceholder').then((component) => ({
        default: component.BlogPreviewPlaceholder
    })),
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
        'xl:rounded-xl py-16 bg-cover bg-center',
        "after:content-[''] after:block after:absolute after:inset-0 after:bg-backgroundDark after:bg-opacity-80 after:xl:rounded-xl",
    );

    return (
        <Webline className="relative px-0 xl:max-w-[1400px]">
            <div
                className={bgImageTwClass}
                style={{ backgroundImage: `url(${blogData?.mainBlogCategoryMainImage?.url})` }}
            >
                {shouldRender ? (
                    <BlogPreview
                        blogArticles={blogPreviewData.blogArticles.edges}
                        blogUrl={blogData?.mainBlogCategoryUrl}
                        fetchingArticles={areBlogArticlesFetching}
                    />
                ) : (
                    <BlogPreviewPlaceholder
                        blogArticles={blogPreviewData.blogArticles.edges}
                        blogUrl={blogData?.mainBlogCategoryUrl}
                    />
                )}
            </div>
        </Webline>
    );
};
