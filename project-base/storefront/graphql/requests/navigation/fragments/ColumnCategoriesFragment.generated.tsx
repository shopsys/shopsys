// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ColumnCategoryFragment } from './ColumnCategoryFragment.generated';
export type TypeColumnCategoriesFragment = { __typename: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, children: Array<{ __typename: 'Category', name: string, slug: string }> }> };

export const ColumnCategoriesFragment = gql`
    fragment ColumnCategoriesFragment on NavigationItemCategoriesByColumns {
  __typename
  columnNumber
  categories {
    ...ColumnCategoryFragment
  }
}
    ${ColumnCategoryFragment}`;