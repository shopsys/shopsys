// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleFlagFragment } from '../../flags/fragments/SimpleFlagFragment.generated';
import { ListedProductPriceFragment } from './ListedProductPriceFragment.generated';
import { AvailabilityFragment } from '../../availabilities/fragments/AvailabilityFragment.generated';
export type TypeListedProductFragment_MainVariant_ = (
  { __typename: 'MainVariant' }
  & Pick<Types.TypeMainVariant, 'variantsCount' | 'id' | 'uuid' | 'slug' | 'fullName' | 'stockQuantity' | 'isAllowedNegativeStock' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'availableStoresCount' | 'catalogNumber' | 'isMainVariant' | 'isInquiryType'>
  & { unit: (
    { __typename: 'Unit' }
    & Pick<Types.TypeUnit, 'name'>
  ), flags: Array<(
    { __typename: 'Flag' }
    & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
  )>, mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'url'>
  )>, price: (
    { __typename: 'ProductPrice' }
    & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'percentageDiscount'>
    & { basicPrice: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat'>
    ) }
  ), availability: (
    { __typename: 'Availability' }
    & Pick<Types.TypeAvailability, 'name' | 'status'>
  ), brand: Types.Maybe<(
    { __typename: 'Brand' }
    & Pick<Types.TypeBrand, 'name'>
  )>, categories: Array<(
    { __typename: 'Category' }
    & Pick<Types.TypeCategory, 'name'>
  )> }
);

export type TypeListedProductFragment_RegularProduct_ = (
  { __typename: 'RegularProduct' }
  & Pick<Types.TypeRegularProduct, 'id' | 'uuid' | 'slug' | 'fullName' | 'stockQuantity' | 'isAllowedNegativeStock' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'availableStoresCount' | 'catalogNumber' | 'isMainVariant' | 'isInquiryType'>
  & { unit: (
    { __typename: 'Unit' }
    & Pick<Types.TypeUnit, 'name'>
  ), flags: Array<(
    { __typename: 'Flag' }
    & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
  )>, mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'url'>
  )>, price: (
    { __typename: 'ProductPrice' }
    & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'percentageDiscount'>
    & { basicPrice: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat'>
    ) }
  ), availability: (
    { __typename: 'Availability' }
    & Pick<Types.TypeAvailability, 'name' | 'status'>
  ), brand: Types.Maybe<(
    { __typename: 'Brand' }
    & Pick<Types.TypeBrand, 'name'>
  )>, categories: Array<(
    { __typename: 'Category' }
    & Pick<Types.TypeCategory, 'name'>
  )> }
);

export type TypeListedProductFragment_Variant_ = (
  { __typename: 'Variant' }
  & Pick<Types.TypeVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'stockQuantity' | 'isAllowedNegativeStock' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'availableStoresCount' | 'catalogNumber' | 'isMainVariant' | 'isInquiryType'>
  & { unit: (
    { __typename: 'Unit' }
    & Pick<Types.TypeUnit, 'name'>
  ), flags: Array<(
    { __typename: 'Flag' }
    & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
  )>, mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'url'>
  )>, price: (
    { __typename: 'ProductPrice' }
    & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'percentageDiscount'>
    & { basicPrice: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat'>
    ) }
  ), availability: (
    { __typename: 'Availability' }
    & Pick<Types.TypeAvailability, 'name' | 'status'>
  ), brand: Types.Maybe<(
    { __typename: 'Brand' }
    & Pick<Types.TypeBrand, 'name'>
  )>, categories: Array<(
    { __typename: 'Category' }
    & Pick<Types.TypeCategory, 'name'>
  )> }
);

export type TypeListedProductFragment = TypeListedProductFragment_MainVariant_ | TypeListedProductFragment_RegularProduct_ | TypeListedProductFragment_Variant_;

export const ListedProductFragment = gql`
    fragment ListedProductFragment on Product {
  __typename
  id
  uuid
  slug
  fullName
  stockQuantity
  isAllowedNegativeStock
  unit {
    __typename
    name
  }
  isSellingDenied
  isCurrentlyOutOfStock
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
  availability {
    ...AvailabilityFragment
  }
  availableStoresCount
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
  isInquiryType
  ... on MainVariant {
    variantsCount
  }
}
    ${SimpleFlagFragment}
${ListedProductPriceFragment}
${AvailabilityFragment}`;