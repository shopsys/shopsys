// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ColumnCategoriesFragment } from './ColumnCategoriesFragment.generated';
export type TypeCategoriesByColumnFragment = (
  { __typename: 'NavigationItem' }
  & Pick<Types.TypeNavigationItem, 'name' | 'link' | 'routeName'>
  & { categoriesByColumns: Array<(
    { __typename: 'NavigationItemCategoriesByColumns' }
    & Pick<Types.TypeNavigationItemCategoriesByColumns, 'columnNumber'>
    & { categories: Array<(
      { __typename: 'Category' }
      & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
      & { mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, children: Array<(
        { __typename: 'Category' }
        & Pick<Types.TypeCategory, 'name' | 'slug'>
      )> }
    )> }
  )> }
);

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