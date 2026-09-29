// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CartItemModificationFragment } from './CartItemModificationFragment.generated';
export type TypeCartItemModificationsFragment = { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, product:
      | { __typename: 'MainVariant', uuid: string, fullName: string }
      | { __typename: 'RegularProduct', uuid: string, fullName: string }
      | { __typename: 'Variant', uuid: string, fullName: string }
     }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, product:
      | { __typename: 'MainVariant', uuid: string, fullName: string }
      | { __typename: 'RegularProduct', uuid: string, fullName: string }
      | { __typename: 'Variant', uuid: string, fullName: string }
     }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, product:
      | { __typename: 'MainVariant', uuid: string, fullName: string }
      | { __typename: 'RegularProduct', uuid: string, fullName: string }
      | { __typename: 'Variant', uuid: string, fullName: string }
     }>, cartItemsWithRemovedAdditionalServices: Array<{ __typename: 'CartItem', uuid: string, product:
      | { __typename: 'MainVariant', uuid: string, fullName: string }
      | { __typename: 'RegularProduct', uuid: string, fullName: string }
      | { __typename: 'Variant', uuid: string, fullName: string }
     }>, cartItemsWithModifiedAdditionalServicePrices: Array<{ __typename: 'CartItem', uuid: string, product:
      | { __typename: 'MainVariant', uuid: string, fullName: string }
      | { __typename: 'RegularProduct', uuid: string, fullName: string }
      | { __typename: 'Variant', uuid: string, fullName: string }
     }> };

export const CartItemModificationsFragment = gql`
    fragment CartItemModificationsFragment on CartItemModificationsResult {
  __typename
  noLongerListableCartItems {
    ...CartItemModificationFragment
  }
  cartItemsWithModifiedPrice {
    ...CartItemModificationFragment
  }
  cartItemsWithChangedQuantity {
    ...CartItemModificationFragment
  }
  cartItemsWithRemovedAdditionalServices {
    ...CartItemModificationFragment
  }
  cartItemsWithModifiedAdditionalServicePrices {
    ...CartItemModificationFragment
  }
}
    ${CartItemModificationFragment}`;