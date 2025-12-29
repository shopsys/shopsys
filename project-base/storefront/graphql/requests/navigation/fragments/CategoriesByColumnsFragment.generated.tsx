// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ColumnCategoriesFragment } from './ColumnCategoriesFragment.generated';
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