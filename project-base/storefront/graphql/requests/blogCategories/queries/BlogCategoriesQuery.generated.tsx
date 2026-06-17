// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogCategoriesFragment } from '../fragments/BlogCategoriesFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBlogCategoriesVariables = Exact<{ [key: string]: never; }>;


export type TypeBlogCategories = { blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { name: string } | null }>, parent: { name: string } | null }>, parent: { name: string } | null }>, parent: { name: string } | null }>, parent: { name: string } | null }> };


export const BlogCategoriesDocument = gql`
    query BlogCategories @redisCache(ttl: 3600) {
  blogCategories {
    ...BlogCategoriesFragment
  }
}
    ${BlogCategoriesFragment}`;

export function useBlogCategories(options?: Omit<Urql.UseQueryArgs<TypeBlogCategoriesVariables>, 'query'>) {
  return Urql.useQuery<TypeBlogCategories, TypeBlogCategoriesVariables>({ query: BlogCategoriesDocument, ...options });
};