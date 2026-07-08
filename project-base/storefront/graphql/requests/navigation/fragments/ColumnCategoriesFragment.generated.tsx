// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ColumnCategoryFragment } from './ColumnCategoryFragment.generated';
export type TypeColumnCategoriesFragment = (
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
);

export const ColumnCategoriesFragment = gql`
    fragment ColumnCategoriesFragment on NavigationItemCategoriesByColumns {
  __typename
  columnNumber
  categories {
    ...ColumnCategoryFragment
  }
}
    ${ColumnCategoryFragment}`;