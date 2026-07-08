// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { NavigationSubCategoriesLinkFragment } from '../../categories/fragments/NavigationSubCategoriesLinkFragment.generated';
export type TypeColumnCategoryFragment = (
  { __typename: 'Category' }
  & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
  & { mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, children: Array<(
    { __typename: 'Category' }
    & Pick<Types.TypeCategory, 'name' | 'slug'>
  )> }
);

export const ColumnCategoryFragment = gql`
    fragment ColumnCategoryFragment on Category {
  __typename
  uuid
  name
  slug
  mainImage {
    ...ImageFragment
  }
  ...NavigationSubCategoriesLinkFragment
}
    ${ImageFragment}
${NavigationSubCategoriesLinkFragment}`;