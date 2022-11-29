import { BlogArticlesList } from './BlogArticlesList/BlogArticlesList';
import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { usePaginationContext } from 'components/Blocks/Pagination/usePaginationContext';
import { mapBlogArticleConnection } from 'connectors/articleInterface/blogArticle/BlogArticle';
import { useBlogCategoryArticlesApi } from 'graphql/generated';
import { FC, useMemo, useRef } from 'react';
import { ListedBlogArticleType } from 'types/blogArticle';

type BlogCategoryArticlesWrapperProps = {
    uuid: string;
};

export const BlogCategoryArticlesWrapper: FC<BlogCategoryArticlesWrapperProps> = ({ uuid }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const [{ endCursor }] = usePaginationContext();

    const [{ data }] = useBlogCategoryArticlesApi({
        variables: { uuid, endCursorForPagination: endCursor ?? '', pageSize: DEFAULT_PAGE_SIZE },
    });

    const mappedArticles: ListedBlogArticleType[] = useMemo(
        () =>
            data?.blogCategory?.blogArticles.edges !== undefined
                ? mapBlogArticleConnection(data.blogCategory.blogArticles)?.edges ?? []
                : [],
        [data?.blogCategory?.blogArticles],
    );

    return (
        <>
            <BlogArticlesList blogArticles={mappedArticles} />
            <Pagination
                containerWrapRef={containerWrapRef}
                totalCount={data?.blogCategory?.blogArticles.totalCount ?? 0}
            />
        </>
    );
};
