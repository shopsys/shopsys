import { BlogArticlesList } from './BlogArticlesList';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { SkeletonModuleArticleBlog } from 'components/Blocks/Skeleton/SkeletonModuleArticleBlog';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { useBlogCategoryArticles } from 'graphql/requests/blogCategories/queries/BlogCategoryArticlesQuery.generated';
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

    const [{ data, fetching: areBlogCategoryArticlesFetching }] = useBlogCategoryArticles({
        variables: { uuid, endCursor: getEndCursor(currentPage), pageSize: DEFAULT_PAGE_SIZE },
    });

    const mappedArticles = useMemo(
        () => mapConnectionEdges<TypeListedBlogArticleFragment>(data?.blogCategory?.blogArticles.edges),
        [data?.blogCategory?.blogArticles.edges],
    );

    return (
        <>
            {!!mappedArticles?.length && !areBlogCategoryArticlesFetching ? (
                <BlogArticlesList blogArticles={mappedArticles} />
            ) : (
                <div className="flex flex-col gap-10">
                    {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
                        <SkeletonModuleArticleBlog key={index} />
                    ))}
                </div>
            )}

            <Pagination
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={data?.blogCategory?.blogArticles.totalCount ?? 0}
            />
        </>
    );
};
