import { BlogArticlesList } from './BlogArticlesList';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { SkeletonModuleArticleBlog } from 'components/Blocks/Skeleton/SkeletonModuleArticleBlog';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { useBlogCategoryArticles } from 'graphql/requests/blogCategories/queries/BlogCategoryArticlesQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { RefObject, useMemo } from 'react';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';

type BlogCategoryArticlesWrapperProps = {
    uuid: string;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const BlogCategoryArticlesWrapper: FC<BlogCategoryArticlesWrapperProps> = ({
    uuid,
    paginationScrollTargetRef,
}) => {
    const currentPage = useCurrentPageQuery();
    const { t } = useTranslation();

    const [{ data: blogCategoryArticlesData, fetching: areBlogCategoryArticlesFetching }] = useBlogCategoryArticles({
        variables: {
            uuid,
            endCursor: getEndCursor(currentPage, 0, DEFAULT_BLOG_PAGE_SIZE),
            pageSize: DEFAULT_BLOG_PAGE_SIZE,
        },
    });

    const mappedArticles = useMemo(
        () =>
            mapConnectionEdges<TypeListedBlogArticleFragment>(
                blogCategoryArticlesData?.blogCategory?.blogArticles.edges,
            ),
        [blogCategoryArticlesData?.blogCategory?.blogArticles.edges],
    );

    const articlesContent = mappedArticles?.length ? (
        <BlogArticlesList blogArticles={mappedArticles} />
    ) : (
        <div>{t('Sorry, there are no articles in this category at the moment.')}</div>
    );

    return (
        <div className="flex flex-col gap-y-6 md:gap-y-10">
            {areBlogCategoryArticlesFetching ? (
                <div className="flex flex-col gap-y-5">
                    {createEmptyArray(DEFAULT_BLOG_PAGE_SIZE).map((_, index) => (
                        <SkeletonModuleArticleBlog key={index} />
                    ))}
                </div>
            ) : (
                articlesContent
            )}

            <Pagination
                pageSize={DEFAULT_BLOG_PAGE_SIZE}
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={blogCategoryArticlesData?.blogCategory?.blogArticles.totalCount ?? 0}
                type="blog"
            />
        </div>
    );
};
