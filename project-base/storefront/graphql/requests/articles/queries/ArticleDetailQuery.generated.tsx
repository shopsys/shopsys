// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ArticleDetailFragment } from '../../articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeArticleDetailQueryVariables = Types.Exact<{
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type TypeArticleDetailQuery = (
  { __typename?: 'Query' }
  & { article: Types.Maybe<{ __typename?: 'ArticleLink' } | (
    { __typename: 'ArticleSite' }
    & Pick<Types.TypeArticleSite, 'uuid' | 'slug' | 'placement' | 'text' | 'seoTitle' | 'seoMetaDescription' | 'createdAt' | 'seoH1'>
    & { articleName: Types.TypeArticleSite['name'] }
    & { breadcrumb: Array<(
      { __typename: 'Link' }
      & Pick<Types.TypeLink, 'name' | 'slug'>
    )> }
  )> }
);


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