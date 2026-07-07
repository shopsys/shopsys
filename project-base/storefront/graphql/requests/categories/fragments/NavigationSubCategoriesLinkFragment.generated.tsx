// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeNavigationSubCategoriesLinkFragment = { __typename: 'Category', uuid: string, children: Array<{ __typename: 'Category', name: string, slug: string }> };

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