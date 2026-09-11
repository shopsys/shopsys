// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SeoPageFragment } from '../fragments/SeoPageFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSeoPageQueryVariables = Exact<{
  pageSlug: string;
}>;


export type TypeSeoPageQuery = { seoPage: { __typename: 'SeoPage', ogTitle: string | null, ogDescription: string | null, seo: { __typename: 'SeoAttributes', title: string | null, metaDescription: string | null, h1: string | null, metaRobots: string | null, canonicalUrl: string | null }, ogImage: { __typename: 'Image', name: string | null, url: string } | null, hreflangLinks: Array<{ hreflang: string, href: string }> } | null };


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