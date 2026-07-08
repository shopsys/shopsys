// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedProductFragment } from '../../products/fragments/ListedProductFragment.generated';
export type TypeCategoryBestsellerFragment_MainVariant_ = (
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

export type TypeCategoryBestsellerFragment_RegularProduct_ = (
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

export type TypeCategoryBestsellerFragment_Variant_ = (
  { __typename: 'Variant' }
  & Pick<Types.TypeVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'stockQuantity' | 'isAllowedNegativeStock' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'availableStoresCount' | 'catalogNumber' | 'isMainVariant' | 'isInquiryType'>
  & { mainVariant: Types.Maybe<(
    { __typename?: 'MainVariant' }
    & Pick<Types.TypeMainVariant, 'slug'>
  )>, unit: (
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

export type TypeCategoryBestsellerFragment = TypeCategoryBestsellerFragment_MainVariant_ | TypeCategoryBestsellerFragment_RegularProduct_ | TypeCategoryBestsellerFragment_Variant_;

export const CategoryBestsellerFragment = gql`
    fragment CategoryBestsellerFragment on Product {
  ...ListedProductFragment
  ... on Variant {
    mainVariant {
      slug
    }
  }
}
    ${ListedProductFragment}`;