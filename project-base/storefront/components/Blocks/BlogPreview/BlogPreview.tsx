import { BlogPreviewMain } from './BlogPreviewMain';
import { BlogPreviewSide } from './BlogPreviewSide';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowRightIcon } from 'components/Basic/Icon/ArrowRightIcon';
import { SkeletonModuleMagazine } from 'components/Blocks/Skeleton/SkeletonModuleMagazine';
import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { useBlogArticlesQuery } from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { useBlogUrlQuery } from 'graphql/requests/blogCategories/queries/BlogUrlQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';

export const BlogPreview: FC = () => {
    const { t } = useTranslation();
    const [{ data: blogPreviewData, fetching: fetchingArticles }] = useBlogArticlesQuery({
        variables: BLOG_PREVIEW_VARIABLES,
    });
    const [{ data: blogUrlData, fetching: fetchingBlogUrl }] = useBlogUrlQuery();
    const blogUrl = blogUrlData?.blogCategories[0].link;

    const blogMainItems = useMemo(
        () => mapConnectionEdges<TypeListedBlogArticleFragment>(blogPreviewData?.blogArticles.edges?.slice(0, 2)),
        [blogPreviewData?.blogArticles.edges],
    );
    const blogSideItems = useMemo(
        () => mapConnectionEdges<TypeListedBlogArticleFragment>(blogPreviewData?.blogArticles.edges?.slice(2)),
        [blogPreviewData?.blogArticles.edges],
    );

    return (
        <div className="py-10 vl:py-16 vl:pb-20">
            <div className="mb-5 flex flex-wrap items-baseline">
                <h2 className="mr-8 mb-2 transform-none text-3xl font-bold leading-9 text-creamWhite">
                    {t('Shopsys magazine')}
                </h2>

                {!!blogUrl && (
                    <ExtendedNextLink
                        className="mb-2 flex items-center font-bold uppercase text-creamWhite no-underline hover:text-creamWhite hover:no-underline"
                        href={blogUrl}
                        type="blogCategory"
                    >
                        <>
                            {t('View all')}
                            <ArrowRightIcon className="relative top-0 ml-2 text-xs text-creamWhite" />
                        </>
                    </ExtendedNextLink>
                )}
            </div>

            {(fetchingArticles || fetchingBlogUrl) && <SkeletonModuleMagazine />}

            {!fetchingArticles && !fetchingBlogUrl && !!(blogMainItems || blogSideItems) && (
                <div className="flex flex-col gap-16 vl:flex-row  vl:justify-between">
                    {!!blogMainItems && (
                        <div className="flex flex-1 gap-6 vl:gap-16">
                            <BlogPreviewMain articles={blogMainItems} />
                        </div>
                    )}

                    {!!blogSideItems && <BlogPreviewSide articles={blogSideItems} />}
                </div>
            )}
        </div>
    );
};
