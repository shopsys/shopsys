import { BlogArticlesList } from './BlogArticlesList';
import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { ListedBlogArticleFragmentApi, useBlogCategoryArticlesApi } from 'graphql/generated';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { useQueryParams } from 'hooks/useQueryParams';
import { useMemo, useRef } from 'react';

type BlogCategoryArticlesWrapperProps = {
    uuid: string;
};

export const BlogCategoryArticlesWrapper: FC<BlogCategoryArticlesWrapperProps> = ({ uuid }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const { currentPage } = useQueryParams();

    const [{ data }] = useBlogCategoryArticlesApi({
        variables: { uuid, endCursor: getEndCursor(currentPage), pageSize: DEFAULT_PAGE_SIZE },
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
