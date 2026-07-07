// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleFlagFragment } from '../../flags/fragments/SimpleFlagFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { AvailabilityFragment } from '../../availabilities/fragments/AvailabilityFragment.generated';
import { ProductPriceFragment } from '../../products/fragments/ProductPriceFragment.generated';
import { SimpleBrandFragment } from '../../brands/fragments/SimpleBrandFragment.generated';
import { ParameterFragment } from '../../parameters/fragments/ParameterFragment.generated';
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

export type TypeCartItemFragment = { __typename: 'CartItem', uuid: string, quantity: number, type: Types.TypeCartItemTypeEnum, freeQuantity: number, product:
    | { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
    | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
    | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, mainVariant: { slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
   };

export const CartItemFragment = gql`
    fragment CartItemFragment on CartItem {
  __typename
  uuid
  quantity
  type
  freeQuantity
  product {
    __typename
    id
    uuid
    slug
    ... on Variant {
      mainVariant {
        slug
      }
    }
    fullName
    catalogNumber
    isInquiryType
    flags {
      ...SimpleFlagFragment
    }
    mainImage {
      ...ImageFragment
    }
    stockQuantity
    isAllowedNegativeStock
    availability {
      ...AvailabilityFragment
    }
    promotionBuyQuantity
    promotionFreeQuantity
    price {
      ...ProductPriceFragment
    }
    giftPrice {
      ...ProductPriceFragment
    }
    availableStoresCount
    unit {
      name
    }
    brand {
      ...SimpleBrandFragment
    }
    categories {
      name
    }
    parameters {
      ...ParameterFragment
    }
    vatPercent
  }
}
    ${SimpleFlagFragment}
${ImageFragment}
${AvailabilityFragment}
${ProductPriceFragment}
${SimpleBrandFragment}
${ParameterFragment}`;