// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CartItemModificationsFragment } from './CartItemModificationsFragment.generated';
import { CartTransportModificationsFragment } from './CartTransportModificationsFragment.generated';
import { CartPaymentModificationsFragment } from './CartPaymentModificationsFragment.generated';
import { CartPromoCodeModificationsFragment } from './CartPromoCodeModificationsFragment.generated';
import { CartGiftVoucherModificationsFragment } from './CartGiftVoucherModificationsFragment.generated';
export type TypeCartModificationsFragment = { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, product:
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
       }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, giftVoucherModifications: { __typename: 'CartGiftVoucherModificationsResult', noLongerApplicableGiftVouchers: Array<string> }, multipleAddedProductModifications: { notAddedProducts: Array<
      | { fullName: string }
      | { fullName: string }
      | { fullName: string }
    > } };

export const CartModificationsFragment = gql`
    fragment CartModificationsFragment on CartModificationsResult {
  __typename
  itemModifications {
    ...CartItemModificationsFragment
  }
  transportModifications {
    ...CartTransportModificationsFragment
  }
  paymentModifications {
    ...CartPaymentModificationsFragment
  }
  promoCodeModifications {
    ...CartPromoCodeModificationsFragment
  }
  giftVoucherModifications {
    ...CartGiftVoucherModificationsFragment
  }
  someProductWasRemovedFromEshop
  multipleAddedProductModifications {
    notAddedProducts {
      fullName
    }
  }
}
    ${CartItemModificationsFragment}
${CartTransportModificationsFragment}
${CartPaymentModificationsFragment}
${CartPromoCodeModificationsFragment}
${CartGiftVoucherModificationsFragment}`;