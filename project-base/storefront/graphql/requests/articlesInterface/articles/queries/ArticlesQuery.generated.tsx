// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { SimpleNotBlogArticleFragment } from '../fragments/SimpleNotBlogArticleFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeArticlesQueryVariables = Types.Exact<{
  placement?: Types.InputMaybe<Array<Types.TypeArticlePlacementTypeEnum> | Types.TypeArticlePlacementTypeEnum>;
  first?: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;


export type TypeArticlesQuery = (
  { __typename?: 'Query' }
  & { articles: (
    { __typename?: 'ArticleConnection' }
    & { edges: Types.Maybe<Array<Types.Maybe<(
      { __typename: 'ArticleEdge' }
      & { node: Types.Maybe<(
        { __typename: 'ArticleLink' }
        & Pick<Types.TypeArticleLink, 'uuid' | 'name' | 'url' | 'placement' | 'external'>
      ) | (
        { __typename: 'ArticleSite' }
        & Pick<Types.TypeArticleSite, 'uuid' | 'name' | 'slug' | 'placement' | 'external'>
      )> }
    )>>> }
  ) }
);


export const ArticlesQueryDocument = gql`
    query ArticlesQuery($placement: [ArticlePlacementTypeEnum!], $first: Int) @redisCache(ttl: 3600) {
  articles(placement: $placement, first: $first) {
    edges {
      __typename
      node {
        ...SimpleNotBlogArticleFragment
      }
    }
  }
}
    ${SimpleNotBlogArticleFragment}`;

export function useArticlesQuery(options?: Omit<Urql.UseQueryArgs<TypeArticlesQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeArticlesQuery, TypeArticlesQueryVariables>({ query: ArticlesQueryDocument, ...options });
};