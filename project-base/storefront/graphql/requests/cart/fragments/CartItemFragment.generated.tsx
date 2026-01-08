// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleFlagFragment } from '../../flags/fragments/SimpleFlagFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { AvailabilityFragment } from '../../availabilities/fragments/AvailabilityFragment.generated';
import { ProductPriceFragment } from '../../products/fragments/ProductPriceFragment.generated';
import { SimpleBrandFragment } from '../../brands/fragments/SimpleBrandFragment.generated';
export type TypeCartItemFragment = { __typename: 'CartItem', uuid: string, quantity: number, type: Types.TypeCartItemTypeEnum, freeQuantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } };

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
    vatPercent
  }
}
    ${SimpleFlagFragment}
${ImageFragment}
${AvailabilityFragment}
${ProductPriceFragment}
${SimpleBrandFragment}`;