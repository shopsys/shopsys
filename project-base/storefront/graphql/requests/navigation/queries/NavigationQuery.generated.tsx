// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CategoriesByColumnFragment } from '../fragments/CategoriesByColumnsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeFriendlyUrlRouteEnum =
  | 'FRONT_ARTICLE_DETAIL'
  | 'FRONT_BLOGARTICLE_DETAIL'
  | 'FRONT_BLOGCATEGORY_DETAIL'
  | 'FRONT_BRAND_DETAIL'
  | 'FRONT_CATEGORY_SEO'
  | 'FRONT_FLAG_DETAIL'
  | 'FRONT_PRODUCT_DETAIL'
  | 'FRONT_PRODUCT_LIST'
  | 'FRONT_STORES_DETAIL';

export type TypeNavigationQueryVariables = Exact<{ [key: string]: never; }>;


export type TypeNavigationQuery = { navigation: Array<{ __typename: 'NavigationItem', name: string, type: string, link: string | null, routeName: Types.TypeFriendlyUrlRouteEnum | null, categoriesByColumns: Array<{ __typename: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, children: Array<{ __typename: 'Category', name: string, slug: string }> }> }> }> };


export const NavigationQueryDocument = gql`
    query NavigationQuery @redisCache(ttl: 3600) {
  navigation {
    ...CategoriesByColumnFragment
  }
}
    ${CategoriesByColumnFragment}`;

export function useNavigationQuery(options?: Omit<Urql.UseQueryArgs<TypeNavigationQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeNavigationQuery, TypeNavigationQueryVariables>({ query: NavigationQueryDocument, ...options });
};