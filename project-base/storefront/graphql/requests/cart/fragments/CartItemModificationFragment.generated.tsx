// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeCartItemModificationFragment = { __typename: 'CartItem', uuid: string, product:
    | { __typename: 'MainVariant', uuid: string, fullName: string }
    | { __typename: 'RegularProduct', uuid: string, fullName: string }
    | { __typename: 'Variant', uuid: string, fullName: string }
   };

export const CartItemModificationFragment = gql`
    fragment CartItemModificationFragment on CartItem {
  __typename
  uuid
  product {
    __typename
    uuid
    fullName
  }
}
    `;