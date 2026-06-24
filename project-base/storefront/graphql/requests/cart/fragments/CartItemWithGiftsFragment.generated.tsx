// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CartItemFragment } from './CartItemFragment.generated';
import { ProductGiftFragment } from '../../products/fragments/ProductGiftFragment.generated';
/** Product Availability statuses */
export type TypeAvailabilityStatusEnum =
  /** Product availability status in stock */
  | 'InStock'
  /** Product availability status out of stock */
  | 'OutOfStock';

/** One of possible types of the cart item */
export type TypeCartItemTypeEnum =
  | 'product'
  | 'productGift';

/** Represents the type of the parameter */
export type TypeParameterTypeEnum =
  | 'CHECKBOX'
  | 'COLOR'
  | 'SLIDER';

export type TypeCartItemWithGiftsFragment = { __typename: 'CartItem', uuid: string, quantity: number, type: Types.TypeCartItemTypeEnum, freeQuantity: number, product:
    | { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, gifts: Array<
        | { uuid: string, name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } } }
        | { uuid: string, name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } } }
        | { uuid: string, name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } } }
      >, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
    | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, gifts: Array<
        | { uuid: string, name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } } }
        | { uuid: string, name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } } }
        | { uuid: string, name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } } }
      >, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
    | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, mainVariant: { slug: string } | null, gifts: Array<
        | { uuid: string, name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } } }
        | { uuid: string, name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } } }
        | { uuid: string, name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } } }
      >, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
   };

export const CartItemWithGiftsFragment = gql`
    fragment CartItemWithGiftsFragment on CartItem {
  ...CartItemFragment
  product {
    gifts {
      ...ProductGiftFragment
    }
  }
}
    ${CartItemFragment}
${ProductGiftFragment}`;