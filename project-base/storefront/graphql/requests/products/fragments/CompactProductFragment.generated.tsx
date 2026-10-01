// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleFlagFragment } from '../../flags/fragments/SimpleFlagFragment.generated';
import { ListedProductPriceFragment } from './ListedProductPriceFragment.generated';
import { AvailabilityFragment } from '../../availabilities/fragments/AvailabilityFragment.generated';
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

export type TypeCompactProductFragment_MainVariant = { __typename: 'MainVariant', variantsCount: number, id: number, uuid: string, slug: string, fullName: string, isSellingDenied: boolean, expectedRestockingDate: string | null, catalogNumber: string, isMainVariant: boolean, productType: Types.TypeProductTypeEnum, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null };

export type TypeCompactProductFragment_RegularProduct = { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, isSellingDenied: boolean, expectedRestockingDate: string | null, catalogNumber: string, isMainVariant: boolean, productType: Types.TypeProductTypeEnum, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null };

export type TypeCompactProductFragment_Variant = { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, isSellingDenied: boolean, expectedRestockingDate: string | null, catalogNumber: string, isMainVariant: boolean, productType: Types.TypeProductTypeEnum, mainVariant: { __typename: 'MainVariant', reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null };

export type TypeCompactProductFragment =
  | TypeCompactProductFragment_MainVariant
  | TypeCompactProductFragment_RegularProduct
  | TypeCompactProductFragment_Variant
;

export const CompactProductFragment = gql`
    fragment CompactProductFragment on Product {
  __typename
  id
  uuid
  slug
  fullName
  isSellingDenied
  flags {
    ...SimpleFlagFragment
  }
  mainImage {
    __typename
    url
  }
  price {
    ...ListedProductPriceFragment
  }
  expectedRestockingDate
  availability {
    ...AvailabilityFragment
  }
  catalogNumber
  brand {
    __typename
    name
  }
  categories {
    __typename
    name
  }
  isMainVariant
  reviewsSummary {
    __typename
    averageRating
    totalCount
  }
  productType
  ... on MainVariant {
    variantsCount
  }
  ... on Variant {
    mainVariant {
      __typename
      reviewsSummary {
        __typename
        averageRating
        totalCount
      }
    }
  }
}
    ${SimpleFlagFragment}
${ListedProductPriceFragment}
${AvailabilityFragment}`;