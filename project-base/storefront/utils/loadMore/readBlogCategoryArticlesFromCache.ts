import { DocumentNode } from 'graphql';
import { TypeBlogArticleConnectionFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.generated';
import { TypeBlogCategoryArticles } from 'graphql/requests/blogCategories/queries/BlogCategoryArticlesQuery.generated';
import { Client } from 'urql';

export const readBlogCategoryArticlesFromCache = (
    queryDocument: DocumentNode,
    client: Client,
    uuid: string,
    endCursor: string,
    pageSize: number,
): {
    blogCategoryArticles: TypeBlogArticleConnectionFragment['edges'] | undefined;
    hasNextPage: boolean;
} => {
    const dataFromCache = client.readQuery<TypeBlogCategoryArticles>(queryDocument, {
        uuid,
        endCursor,
        pageSize,
    })?.data?.blogCategory?.blogArticles;

    return {
        blogCategoryArticles: dataFromCache?.edges,
        hasNextPage: !!dataFromCache?.pageInfo.hasNextPage,
    };
};
