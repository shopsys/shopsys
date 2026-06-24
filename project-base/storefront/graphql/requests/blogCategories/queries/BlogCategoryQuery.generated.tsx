// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogCategoryDetailFragment } from '../fragments/BlogCategoryDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBlogCategoryQueryVariables = Exact<{
  urlSlug?: string | null | undefined;
}>;


export type TypeBlogCategoryQuery = { blogCategory: { __typename: 'BlogCategory', uuid: string, name: string, seoTitle: string | null, seoMetaDescription: string | null, description: string | null, articlesTotalCount: number, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, hreflangLinks: Array<{ hreflang: string, href: string }> } | null };


export const BlogCategoryQueryDocument = gql`
    query BlogCategoryQuery($urlSlug: String) @friendlyUrl {
  blogCategory(urlSlug: $urlSlug) {
    ...BlogCategoryDetailFragment
  }
}
    ${BlogCategoryDetailFragment}`;

export function useBlogCategoryQuery(options?: Omit<Urql.UseQueryArgs<TypeBlogCategoryQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeBlogCategoryQuery, TypeBlogCategoryQueryVariables>({ query: BlogCategoryQueryDocument, ...options });
};