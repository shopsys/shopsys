// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ColumnCategoriesFragment } from './ColumnCategoriesFragment.generated';
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

export type TypeCategoriesByColumnFragment = { __typename: 'NavigationItem', name: string, link: string, routeName: Types.TypeFriendlyUrlRouteEnum | null, categoriesByColumns: Array<{ __typename: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, children: Array<{ __typename: 'Category', name: string, slug: string }> }> }> };

export const CategoriesByColumnFragment = gql`
    fragment CategoriesByColumnFragment on NavigationItem {
  __typename
  name
  link
  routeName
  categoriesByColumns {
    ...ColumnCategoriesFragment
  }
}
    ${ColumnCategoriesFragment}`;