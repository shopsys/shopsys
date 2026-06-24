// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { SimpleNotBlogArticleFragment } from '../fragments/SimpleNotBlogArticleFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Possible placements of an article (used as an input for 'articles' query) */
export type TypeArticlePlacementTypeEnum =
  /** Articles in 1st footer column */
  | 'footer1'
  /** Articles in 2nd footer column */
  | 'footer2'
  /** Articles in 3rd footer column */
  | 'footer3'
  /** Articles in 4th footer column */
  | 'footer4'
  /** Articles without specific placement */
  | 'none';

export type TypeArticlesQueryVariables = Exact<{
  placement?: Array<Types.TypeArticlePlacementTypeEnum> | Types.TypeArticlePlacementTypeEnum | null | undefined;
  first?: number | null | undefined;
}>;


export type TypeArticlesQuery = { articles: { edges: Array<{ __typename: 'ArticleEdge', node:
        | { __typename: 'ArticleLink', uuid: string, name: string, url: string, placement: string, external: boolean }
        | { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean }
       | null } | null> | null } };


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