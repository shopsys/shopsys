import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BlogPreviewMain } from './BlogPreviewMain';
import { Icon } from 'components/Basic/Icon/Icon';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useMemo } from 'react';
import { BlogPreviewSide } from './BlogPreviewSide';
import { ListedBlogArticleFragmentApi } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { useBlogArticlesQueryApi } from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { useBlogUrlQueryApi } from 'graphql/requests/blogCategories/queries/BlogUrlQuery.generated';

export const BLOG_PREVIEW_VARIABLES = { first: 6, onlyHomepageArticles: true };
const TEST_IDENTIFIER = 'blocks-blogpreview';

export const BlogPreview: FC = () => {
    const t = useTypedTranslationFunction();
    const [{ data: blogPreviewData }] = useBlogArticlesQueryApi({ variables: BLOG_PREVIEW_VARIABLES });
    const [{ data: blogUrlData }] = useBlogUrlQueryApi();
    const blogUrl = blogUrlData?.blogCategories[0].link;

    const blogMainItems = useMemo(
        () => mapConnectionEdges<ListedBlogArticleFragmentApi>(blogPreviewData?.blogArticles.edges?.slice(0, 2)),
        [blogPreviewData?.blogArticles.edges],
    );
    const blogSideItems = useMemo(
        () => mapConnectionEdges<ListedBlogArticleFragmentApi>(blogPreviewData?.blogArticles.edges?.slice(2)),
        [blogPreviewData?.blogArticles.edges],
    );

    return (
        <div className="py-10 vl:py-16 vl:pb-20" data-testid={TEST_IDENTIFIER}>
            <div className="mb-5 flex flex-wrap items-baseline">
                <h2 className="mr-8 mb-2 transform-none text-3xl font-bold leading-9 text-creamWhite">
                    {t('Shopsys magazine')}
                </h2>

                {!!blogUrl && (
                    <ExtendedNextLink
                        type="blogCategory"
                        href={blogUrl}
                        className="mb-2 flex items-center font-bold uppercase text-creamWhite no-underline hover:text-creamWhite hover:no-underline"
                    >
                        <>
                            {t('View all')}
                            <Icon
                                iconType="icon"
                                icon="ArrowRight"
                                className="relative top-0 ml-2 text-xs text-creamWhite"
                            />
                        </>
                    </ExtendedNextLink>
                )}
            </div>

            <div className="flex flex-col gap-16 vl:flex-row  vl:justify-between">
                {!!blogMainItems && (
                    <div className="flex flex-1 gap-6 vl:gap-16">
                        <BlogPreviewMain articles={blogMainItems} />
                    </div>
                )}

                {!!blogSideItems && (
                    <div className="grid snap-x snap-mandatory auto-cols-[80%] gap-4 overscroll-x-contain max-vl:grid-flow-col max-vl:overflow-x-auto md:auto-cols-[40%] lg:gap-6 vl:flex vl:basis-1/3 vl:flex-col vl:gap-3">
                        <BlogPreviewSide articles={blogSideItems} />
                    </div>
                )}
            </div>
        </div>
    );
};
