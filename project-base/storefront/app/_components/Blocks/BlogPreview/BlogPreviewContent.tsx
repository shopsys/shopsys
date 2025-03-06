import { BlogPreviewArticles } from './BlogPreviewArticles';
import { BlogPreviewMain } from './BlogPreviewMain';
import { BlogPreviewSide } from './BlogPreviewSide';
import { getBlogArticlesQuery } from 'app/_queries/getBlogArticlesQuery';
import { getSettingsQuery } from 'app/_queries/getSettingsQuery';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Webline } from 'components/Layout/Webline/Webline';
import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.ssr';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { twMergeCustom } from 'utils/twMerge';

export const BlogPreviewContent = async () => {
    const [blogPreviewArticlesResult, settingsResult] = await Promise.allSettled([
        getBlogArticlesQuery(BLOG_PREVIEW_VARIABLES),
        getSettingsQuery(),
    ]);

    const blogPreviewData = blogPreviewArticlesResult.status === 'fulfilled' ? blogPreviewArticlesResult.value : null;
    const mainBlogCategoryData = settingsResult.status === 'fulfilled' ? settingsResult.value : null;

    if (!blogPreviewData?.blogArticles.edges?.length) {
        return null;
    }

    const t = await getTranslation();

    const blogArticles = blogPreviewData.blogArticles.edges;
    const categoryUrl = mainBlogCategoryData?.settings?.mainBlogCategoryData.mainBlogCategoryUrl;

    const blogItems = mapConnectionEdges<TypeListedBlogArticleFragment>(blogArticles);
    const blogMainItems = blogItems?.slice(0, 2);
    const blogSideItems = blogItems?.slice(2);

    return (
        <Webline className="relative px-0 xl:max-w-[1400px]">
            <div
                className={twMergeCustom(
                    'bg-cover bg-center py-16 xl:rounded-xl',
                    "after:bg-backgroundDark/80 after:absolute after:inset-0 after:block after:content-[''] after:xl:rounded-xl",
                )}
                style={{
                    backgroundImage: `url(${mainBlogCategoryData?.settings?.mainBlogCategoryData.mainBlogCategoryMainImage?.url})`,
                }}
            >
                <div className="z-above relative mx-auto w-full max-w-7xl px-5">
                    <div className="mb-5 flex items-center justify-between">
                        <h3 className="text-textInverted">{t('Magazine')}</h3>

                        {!!categoryUrl && (
                            <ExtendedNextLink
                                className="font-secondary text-textInverted hover:text-textInverted text-sm font-semibold tracking-wide no-underline hover:underline"
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
        </Webline>
    );
};
