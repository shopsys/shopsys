// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ArticleDetailFragment } from '../../articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeArticleDetailQueryVariables = Exact<{
  urlSlug?: string | null | undefined;
}>;


export type TypeArticleDetailQuery = { article:
    | { __typename: 'ArticleSite', uuid: string, slug: string, placement: string, text: string | null, seoTitle: string | null, seoMetaDescription: string | null, createdAt: string, seoH1: string | null, articleName: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }> }
    | Record<PropertyKey, never>
   | null };


export const ArticleDetailQueryDocument = gql`
    query ArticleDetailQuery($urlSlug: String) @friendlyUrl {
  article(urlSlug: $urlSlug) {
    ...ArticleDetailFragment
  }
}
    ${ArticleDetailFragment}`;

export function useArticleDetailQuery(options?: Omit<Urql.UseQueryArgs<TypeArticleDetailQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeArticleDetailQuery, TypeArticleDetailQueryVariables>({ query: ArticleDetailQueryDocument, ...options });
};