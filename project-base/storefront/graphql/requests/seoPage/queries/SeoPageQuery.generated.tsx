// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SeoPageFragment } from '../fragments/SeoPageFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSeoPageQueryVariables = Types.Exact<{
  pageSlug: Types.Scalars['String']['input'];
}>;


export type TypeSeoPageQuery = (
  { __typename?: 'Query' }
  & { seoPage: Types.Maybe<(
    { __typename: 'SeoPage' }
    & Pick<Types.TypeSeoPage, 'title' | 'metaDescription' | 'canonicalUrl' | 'ogTitle' | 'ogDescription'>
    & { ogImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, hreflangLinks: Array<(
      { __typename?: 'HreflangLink' }
      & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
    )> }
  )> }
);


export const SeoPageQueryDocument = gql`
    query SeoPageQuery($pageSlug: String!) @redisCache(ttl: 3600) {
  seoPage(pageSlug: $pageSlug) {
    ...SeoPageFragment
  }
}
    ${SeoPageFragment}`;

export function useSeoPageQuery(options: Omit<Urql.UseQueryArgs<TypeSeoPageQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeSeoPageQuery, TypeSeoPageQueryVariables>({ query: SeoPageQueryDocument, ...options });
};