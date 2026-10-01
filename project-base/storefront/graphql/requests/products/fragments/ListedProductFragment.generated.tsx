// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CompactProductFragment } from './CompactProductFragment.generated';
/** Product Availability statuses */
export type TypeAvailabilityStatusEnum =
  /** Product availability status for electronically delivered products */
  | 'Digital'
  /** Product is out of stock with a known expected restocking date */
  | 'ExpectedRestock'
  /** Product availability status in stock */
  | 'InStock'
  /** Product availability status out of stock */
  | 'OutOfStock';

/** One of possible product types */
export type TypeProductTypeEnum =
  /** Basic product */
  | 'BASIC'
  /** Gift voucher delivered by email after the order is paid */
  | 'ELECTRONIC_GIFT_VOUCHER'
  /** Product with inquiry form instead of add to cart button */
  | 'INQUIRY'
  /** Gift voucher delivered printed as a regular product */
  | 'PRINTED_GIFT_VOUCHER';

export type TypeListedProductFragment_MainVariant = { __typename: 'MainVariant', stockQuantity: number | null, isAllowedNegativeStock: boolean, isCurrentlyOutOfStock: boolean, availableStoresCount: number | null, isPersonalPickupOnly: boolean, isInquiryType: boolean, variantsCount: number, id: number, uuid: string, slug: string, fullName: string, isSellingDenied: boolean, expectedRestockingDate: string | null, catalogNumber: string, isMainVariant: boolean, productType: Types.TypeProductTypeEnum, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null };

export type TypeListedProductFragment_RegularProduct = { __typename: 'RegularProduct', stockQuantity: number | null, isAllowedNegativeStock: boolean, isCurrentlyOutOfStock: boolean, availableStoresCount: number | null, isPersonalPickupOnly: boolean, isInquiryType: boolean, id: number, uuid: string, slug: string, fullName: string, isSellingDenied: boolean, expectedRestockingDate: string | null, catalogNumber: string, isMainVariant: boolean, productType: Types.TypeProductTypeEnum, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null };

export type TypeListedProductFragment_Variant = { __typename: 'Variant', stockQuantity: number | null, isAllowedNegativeStock: boolean, isCurrentlyOutOfStock: boolean, availableStoresCount: number | null, isPersonalPickupOnly: boolean, isInquiryType: boolean, id: number, uuid: string, slug: string, fullName: string, isSellingDenied: boolean, expectedRestockingDate: string | null, catalogNumber: string, isMainVariant: boolean, productType: Types.TypeProductTypeEnum, unit: { __typename: 'Unit', name: string }, mainVariant: { __typename: 'MainVariant', reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null };

export type TypeListedProductFragment =
  | TypeListedProductFragment_MainVariant
  | TypeListedProductFragment_RegularProduct
  | TypeListedProductFragment_Variant
;

export const ListedProductFragment = gql`
    fragment ListedProductFragment on Product {
  ...CompactProductFragment
  stockQuantity
  isAllowedNegativeStock
  unit {
    __typename
    name
  }
  isCurrentlyOutOfStock
  availableStoresCount
  isPersonalPickupOnly
  isInquiryType
}
    ${CompactProductFragment}`;