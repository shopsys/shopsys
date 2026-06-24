// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogArticleConnectionFragment } from '../../articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBlogCategoryArticlesVariables = Exact<{
  uuid: string;
  endCursor: string;
  pageSize?: number | null | undefined;
}>;


export type TypeBlogCategoryArticles = { blogCategory: { blogArticles: { __typename: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'BlogArticleEdge', node: { __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: string | null, perex: string | null, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { name: string } | null }> } | null } | null> | null } } | null };


export const BlogCategoryArticlesDocument = gql`
    query BlogCategoryArticles($uuid: Uuid!, $endCursor: String!, $pageSize: Int) {
  blogCategory(uuid: $uuid) {
    blogArticles(after: $endCursor, first: $pageSize) {
      ...BlogArticleConnectionFragment
    }
  }
}
    ${BlogArticleConnectionFragment}`;

export function useBlogCategoryArticles(options: Omit<Urql.UseQueryArgs<TypeBlogCategoryArticlesVariables>, 'query'>) {
  return Urql.useQuery<TypeBlogCategoryArticles, TypeBlogCategoryArticlesVariables>({ query: BlogCategoryArticlesDocument, ...options });
};