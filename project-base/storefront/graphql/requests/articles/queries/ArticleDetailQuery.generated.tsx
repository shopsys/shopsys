// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ArticleDetailFragment } from '../../articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeArticleDetailQueryVariables = Types.Exact<{
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type TypeArticleDetailQuery = { __typename?: 'Query', article: { __typename?: 'ArticleLink' } | { __typename: 'ArticleSite', uuid: string, slug: string, placement: string, text: string | null, seoTitle: string | null, seoMetaDescription: string | null, createdAt: any, seoH1: string | null, articleName: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }> } | null };


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