import { BlogArticlesList } from './BlogArticlesList';
import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { ListedBlogArticleFragmentApi, useBlogCategoryArticlesApi } from 'graphql/generated';
import { createEmptyArray } from 'helpers/arrayUtils';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { useQueryParams } from 'hooks/useQueryParams';
import { useMemo, useRef } from 'react';
import { BlogArticleSkeleton } from '../BlogArticle/BlogArticleSkeleton';

type BlogCategoryArticlesWrapperProps = {
    uuid: string;
};

export const BlogCategoryArticlesWrapper: FC<BlogCategoryArticlesWrapperProps> = ({ uuid }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const { currentPage } = useQueryParams();

    const [{ data, fetching }] = useBlogCategoryArticlesApi({
        variables: { uuid, endCursor: getEndCursor(currentPage), pageSize: DEFAULT_PAGE_SIZE },
    });

    const mappedArticles = useMemo(
        () => mapConnectionEdges<ListedBlogArticleFragmentApi>(data?.blogCategory?.blogArticles.edges),
        [data?.blogCategory?.blogArticles.edges],
    );

    if (!mappedArticles?.length && !fetching) {
        return null;
    }

    return (
        <>
            {!!mappedArticles?.length && !fetching ? (
                <BlogArticlesList blogArticles={mappedArticles} />
            ) : (
                <div className="flex flex-col gap-10">
                    {createEmptyArray(4).map((_, index) => (
                        <BlogArticleSkeleton key={index} />
                    ))}
                </div>
            )}
            <Pagination
                containerWrapRef={containerWrapRef}
                totalCount={data?.blogCategory?.blogArticles.totalCount ?? 0}
            />
        </>
    );
};
