import { BlogPreviewArticles } from './BlogPreviewArticles';
import { BlogPreviewMain } from './BlogPreviewMain';
import { BlogPreviewSide } from './BlogPreviewSide';
import { getBlogArticlesQuery } from 'app/_queries/getBlogArticlesQuery';
import { getSettingsQuery } from 'app/_queries/getSettingsQuery';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.ssr';
import { twJoin } from 'tailwind-merge';
import { mapConnectionEdges } from 'utils/mappers/connection';

export const BlogPreviewContent = async () => {
    const [blogPreviewArticlesResult, settingsResult] = await Promise.allSettled([
        getBlogArticlesQuery(BLOG_PREVIEW_VARIABLES),
        getSettingsQuery(),
    ]);

    const blogPreviewData = blogPreviewArticlesResult.status === 'fulfilled' ? blogPreviewArticlesResult.value : null;
    const mainBlogCategoryData = settingsResult.status === 'fulfilled' ? settingsResult.value.data : null;

    if (!blogPreviewData?.blogArticles.edges?.length) {
        return null;
    }

    const t = await getTranslation();

    const blogArticles = blogPreviewData.blogArticles.edges;
    const categoryUrl = mainBlogCategoryData?.settings?.mainBlogCategoryData.mainBlogCategoryUrl;

    const blogItems = mapConnectionEdges<TypeListedBlogArticleFragment>(blogArticles);
    const blogMainItems = blogItems?.slice(0, 2);
    const blogSideItems = blogItems?.slice(2);

    const bgImageTwClass = twJoin(
        'xl:rounded-xl py-16 bg-cover bg-center relative bg-background-dark/80',
        "after:content-[''] after:block after:absolute after:inset-0 after:bg-background-dark/80 after:xl:rounded-xl",
    );

    return (
        <div
            className={bgImageTwClass}
            style={
                mainBlogCategoryData?.settings?.mainBlogCategoryData.mainBlogCategoryMainImage?.url
                    ? {
                          backgroundImage: `url(${mainBlogCategoryData.settings.mainBlogCategoryData.mainBlogCategoryMainImage.url})`,
                      }
                    : {}
            }
        >
            <div className="z-above xxl:px-[100px] relative mx-auto w-full px-5">
                <div className="mb-5 flex items-center justify-between">
                    <h3 className="text-text-inverted">{t('Magazine')}</h3>

                    {!!categoryUrl && (
                        <ExtendedNextLink
                            className="font-secondary text-text-inverted hover:text-text-inverted text-sm font-semibold tracking-wide no-underline hover:underline"
                            href={categoryUrl}
                            type="blogCategory"
                        >
                            <>{t('All articles')}</>
                        </ExtendedNextLink>
                    )}
                </div>

                <BlogPreviewArticles
                    BlogPreviewMainComponent={blogMainItems ? <BlogPreviewMain articles={blogMainItems} /> : null}
                    BlogPreviewMobileComponent={blogItems ? <BlogPreviewMain articles={blogItems} /> : null}
                    BlogPreviewSideComponent={blogSideItems ? <BlogPreviewSide articles={blogSideItems} /> : null}
                />
            </div>
        </div>
    );
};
