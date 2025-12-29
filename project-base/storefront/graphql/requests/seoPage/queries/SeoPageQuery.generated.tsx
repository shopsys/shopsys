// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SeoPageFragment } from '../fragments/SeoPageFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSeoPageQueryVariables = Types.Exact<{
  pageSlug: Types.Scalars['String']['input'];
}>;


export type TypeSeoPageQuery = { __typename?: 'Query', seoPage: { __typename: 'SeoPage', title: string | null, metaDescription: string | null, canonicalUrl: string | null, ogTitle: string | null, ogDescription: string | null, ogImage: { __typename: 'Image', name: string | null, url: string } | null, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> } | null };


export const SeoPageQueryDocument = gql`
    query SeoPageQuery($pageSlug: String!) {
  seoPage(pageSlug: $pageSlug) {
    ...SeoPageFragment
  }
}
    ${SeoPageFragment}`;

export function useSeoPageQuery(options: Omit<Urql.UseQueryArgs<TypeSeoPageQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeSeoPageQuery, TypeSeoPageQueryVariables>({ query: SeoPageQueryDocument, ...options });
};