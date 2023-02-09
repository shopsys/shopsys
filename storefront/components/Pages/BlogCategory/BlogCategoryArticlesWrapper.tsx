import { BlogArticlesList } from './BlogArticlesList/BlogArticlesList';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { mapBlogArticleConnection } from 'connectors/articleInterface/blogArticle/BlogArticle';
import { BlogCategoryArticlesApi } from 'graphql/generated';
import { FC, useMemo, useRef } from 'react';
import { ListedBlogArticleType } from 'types/blogArticle';

type BlogCategoryArticlesWrapperProps = {
    blogCategoryArticles?: BlogCategoryArticlesApi;
};

export const BlogCategoryArticlesWrapper: FC<BlogCategoryArticlesWrapperProps> = ({ blogCategoryArticles }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);

    const mappedArticles: ListedBlogArticleType[] = useMemo(
        () =>
            blogCategoryArticles?.blogCategory?.blogArticles.edges !== undefined
                ? mapBlogArticleConnection(blogCategoryArticles.blogCategory.blogArticles)?.edges ?? []
                : [],
        [blogCategoryArticles?.blogCategory?.blogArticles],
    );

    return (
        <>
            <BlogArticlesList blogArticles={mappedArticles} />
            <Pagination
                containerWrapRef={containerWrapRef}
                totalCount={blogCategoryArticles?.blogCategory?.blogArticles.totalCount ?? 0}
            />
        </>
    );
};
