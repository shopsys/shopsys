import { BlogPreviewMain } from './BlogPreviewMain';
import { BlogPreviewSide } from './BlogPreviewSide';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowRightIcon } from 'components/Basic/Icon/IconsSvg';
import { ListedBlogArticleFragmentApi, useBlogArticlesQueryApi, useBlogUrlQueryApi } from 'graphql/generated';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';

export const BLOG_PREVIEW_VARIABLES = { first: 6, onlyHomepageArticles: true };
const TEST_IDENTIFIER = 'blocks-blogpreview';

export const BlogPreview: FC = () => {
    const { t } = useTranslation();
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

            <div className="flex flex-col gap-16 vl:flex-row  vl:justify-between">
                {!!blogMainItems && (
                    <div className="flex flex-1 gap-6 vl:gap-16">
                        <BlogPreviewMain articles={blogMainItems} />
                    </div>
                )}

                {!!blogSideItems && <BlogPreviewSide articles={blogSideItems} />}
            </div>
        </div>
    );
};
