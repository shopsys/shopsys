// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedProductFragment } from '../../products/fragments/ListedProductFragment.generated';
import { ParameterFragment } from '../../parameters/fragments/ParameterFragment.generated';
/** Product Availability statuses */
export type TypeAvailabilityStatusEnum =
  /** Product is out of stock with a known expected restocking date */
  | 'ExpectedRestock'
  /** Product availability status in stock */
  | 'InStock'
  /** Product availability status out of stock */
  | 'OutOfStock';

/** Represents the type of the parameter */
export type TypeParameterTypeEnum =
  | 'CHECKBOX'
  | 'COLOR'
  | 'SLIDER';

export type TypeProductInProductListFragment_MainVariant = { __typename: 'MainVariant', imagesCount: number, variantsCount: number, id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }>, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null };

export type TypeProductInProductListFragment_RegularProduct = { __typename: 'RegularProduct', imagesCount: number, id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }>, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null };

export type TypeProductInProductListFragment_Variant = { __typename: 'Variant', imagesCount: number, id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }>, mainVariant: { __typename: 'MainVariant', reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null } | null, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null };

export type TypeProductInProductListFragment =
  | TypeProductInProductListFragment_MainVariant
  | TypeProductInProductListFragment_RegularProduct
  | TypeProductInProductListFragment_Variant
;

export const ProductInProductListFragment = gql`
    fragment ProductInProductListFragment on Product {
  ...ListedProductFragment
  imagesCount
  parameters {
    ...ParameterFragment
  }
}
    ${ListedProductFragment}
${ParameterFragment}`;