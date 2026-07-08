// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeNavigationSubCategoriesLinkFragment = (
  { __typename: 'Category' }
  & Pick<Types.TypeCategory, 'uuid'>
  & { children: Array<(
    { __typename: 'Category' }
    & Pick<Types.TypeCategory, 'name' | 'slug'>
  )> }
);

export const NavigationSubCategoriesLinkFragment = gql`
    fragment NavigationSubCategoriesLinkFragment on Category {
  __typename
  uuid
  children {
    __typename
    name
    slug
  }
}
    `;