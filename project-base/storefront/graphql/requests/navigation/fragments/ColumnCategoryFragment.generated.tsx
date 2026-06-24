// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { NavigationSubCategoriesLinkFragment } from '../../categories/fragments/NavigationSubCategoriesLinkFragment.generated';
export type TypeColumnCategoryFragment = { __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, children: Array<{ __typename: 'Category', name: string, slug: string }> };

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