// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { BlogArticleConnectionFragment } from '../fragments/BlogArticleConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBlogArticlesQueryVariables = Types.Exact<{
  first?: Types.InputMaybe<Types.Scalars['Int']['input']>;
  onlyHomepageArticles?: Types.InputMaybe<Types.Scalars['Boolean']['input']>;
}>;


export type TypeBlogArticlesQuery = { __typename?: 'Query', blogArticles: { __typename: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'BlogArticleEdge', node: { __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any | null, perex: string | null, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }> } | null } | null> | null } };


export const BlogArticlesQueryDocument = gql`
    query BlogArticlesQuery($first: Int, $onlyHomepageArticles: Boolean) @redisCache(ttl: 3600) {
  blogArticles(first: $first, onlyHomepageArticles: $onlyHomepageArticles) {
    ...BlogArticleConnectionFragment
  }
}
    ${BlogArticleConnectionFragment}`;

export function useBlogArticlesQuery(options?: Omit<Urql.UseQueryArgs<TypeBlogArticlesQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeBlogArticlesQuery, TypeBlogArticlesQueryVariables>({ query: BlogArticlesQueryDocument, ...options });
};