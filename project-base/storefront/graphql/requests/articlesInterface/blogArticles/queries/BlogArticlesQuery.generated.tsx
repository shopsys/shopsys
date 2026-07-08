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


export type TypeBlogArticlesQuery = (
  { __typename?: 'Query' }
  & { blogArticles: (
    { __typename: 'BlogArticleConnection' }
    & Pick<Types.TypeBlogArticleConnection, 'totalCount'>
    & { pageInfo: (
      { __typename: 'PageInfo' }
      & Pick<Types.TypePageInfo, 'hasNextPage' | 'hasPreviousPage' | 'endCursor'>
    ), edges: Types.Maybe<Array<Types.Maybe<(
      { __typename: 'BlogArticleEdge' }
      & { node: Types.Maybe<(
        { __typename: 'BlogArticle' }
        & Pick<Types.TypeBlogArticle, 'uuid' | 'name' | 'link' | 'publishDate' | 'perex' | 'slug'>
        & { mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )>, blogCategories: Array<(
          { __typename: 'BlogCategory' }
          & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
          & { parent: Types.Maybe<(
            { __typename?: 'BlogCategory' }
            & Pick<Types.TypeBlogCategory, 'name'>
          )> }
        )> }
      )> }
    )>>> }
  ) }
);


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