import { BlogArticlesList } from './BlogArticlesList';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { SkeletonArticleBlog } from 'components/Blocks/Skeleton/SkeletonArticleBlog';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { ListedBlogArticleFragmentApi, useBlogCategoryArticlesApi } from 'graphql/generated';
import { createEmptyArray } from 'helpers/arrayUtils';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { useQueryParams } from 'hooks/useQueryParams';
import { RefObject, useMemo } from 'react';

type BlogCategoryArticlesWrapperProps = {
    uuid: string;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const BlogCategoryArticlesWrapper: FC<BlogCategoryArticlesWrapperProps> = ({
    uuid,
    paginationScrollTargetRef,
}) => {
    const { currentPage } = useQueryParams();

    const [{ data, fetching }] = useBlogCategoryArticlesApi({
        variables: { uuid, endCursor: getEndCursor(currentPage), pageSize: DEFAULT_PAGE_SIZE },
    });

    const mappedArticles = useMemo(
        () => mapConnectionEdges<ListedBlogArticleFragmentApi>(data?.blogCategory?.blogArticles.edges),
        [data?.blogCategory?.blogArticles.edges],
    );

    return (
        <>
            {!!mappedArticles?.length && !fetching ? (
                <BlogArticlesList blogArticles={mappedArticles} />
            ) : (
                <div className="flex flex-col gap-10">
                    {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
                        <SkeletonArticleBlog key={index} />
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
