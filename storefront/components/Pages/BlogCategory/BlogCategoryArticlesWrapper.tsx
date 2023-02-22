import { BlogArticlesList } from './BlogArticlesList/BlogArticlesList';
import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { usePaginationContext } from 'components/Blocks/Pagination/usePaginationContext';
import { mapConnectionEdges } from 'connectors/connection/Connection';
import { ListedBlogArticleFragmentApi, useBlogCategoryArticlesApi } from 'graphql/generated';
import { useMemo, useRef } from 'react';

type BlogCategoryArticlesWrapperProps = {
    uuid: string;
};

export const BlogCategoryArticlesWrapper: FC<BlogCategoryArticlesWrapperProps> = ({ uuid }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const [{ endCursor }] = usePaginationContext();

    const [{ data }] = useBlogCategoryArticlesApi({
        variables: { uuid, endCursor: endCursor ?? '', pageSize: DEFAULT_PAGE_SIZE },
    });

    const mappedArticles = useMemo(
        () => mapConnectionEdges<ListedBlogArticleFragmentApi>(data?.blogCategory?.blogArticles.edges),
        [data?.blogCategory?.blogArticles.edges],
    );

    return (
        <>
            {mappedArticles !== undefined && <BlogArticlesList blogArticles={mappedArticles} />}
            <Pagination
                containerWrapRef={containerWrapRef}
                totalCount={data?.blogCategory?.blogArticles.totalCount ?? 0}
            />
        </>
    );
};
