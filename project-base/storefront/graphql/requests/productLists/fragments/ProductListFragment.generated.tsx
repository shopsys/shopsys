// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductInProductListFragment } from './ProductInProductListFragment.generated';
export type TypeProductListFragment = (
  { __typename: 'ProductList' }
  & Pick<Types.TypeProductList, 'uuid'>
  & { products: Array<(
    { __typename: 'MainVariant' }
    & Pick<Types.TypeMainVariant, 'variantsCount' | 'id' | 'uuid' | 'slug' | 'fullName' | 'stockQuantity' | 'isAllowedNegativeStock' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'availableStoresCount' | 'catalogNumber' | 'isMainVariant' | 'isInquiryType'>
    & { parameters: Array<(
      { __typename: 'Parameter' }
      & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
      & { unit: Types.Maybe<(
        { __typename: 'Unit' }
        & Pick<Types.TypeUnit, 'name'>
      )>, values: Array<(
        { __typename: 'ParameterValue' }
        & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
        & { colorIcon: Types.Maybe<(
          { __typename?: 'File' }
          & Pick<Types.TypeFile, 'url' | 'anchorText'>
        )> }
      )> }
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
  ) | (
    { __typename: 'RegularProduct' }
    & Pick<Types.TypeRegularProduct, 'id' | 'uuid' | 'slug' | 'fullName' | 'stockQuantity' | 'isAllowedNegativeStock' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'availableStoresCount' | 'catalogNumber' | 'isMainVariant' | 'isInquiryType'>
    & { parameters: Array<(
      { __typename: 'Parameter' }
      & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
      & { unit: Types.Maybe<(
        { __typename: 'Unit' }
        & Pick<Types.TypeUnit, 'name'>
      )>, values: Array<(
        { __typename: 'ParameterValue' }
        & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
        & { colorIcon: Types.Maybe<(
          { __typename?: 'File' }
          & Pick<Types.TypeFile, 'url' | 'anchorText'>
        )> }
      )> }
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
  ) | (
    { __typename: 'Variant' }
    & Pick<Types.TypeVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'stockQuantity' | 'isAllowedNegativeStock' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'availableStoresCount' | 'catalogNumber' | 'isMainVariant' | 'isInquiryType'>
    & { parameters: Array<(
      { __typename: 'Parameter' }
      & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
      & { unit: Types.Maybe<(
        { __typename: 'Unit' }
        & Pick<Types.TypeUnit, 'name'>
      )>, values: Array<(
        { __typename: 'ParameterValue' }
        & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
        & { colorIcon: Types.Maybe<(
          { __typename?: 'File' }
          & Pick<Types.TypeFile, 'url' | 'anchorText'>
        )> }
      )> }
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
  )> }
);

export const ProductListFragment = gql`
    fragment ProductListFragment on ProductList {
  __typename
  uuid
  products {
    ...ProductInProductListFragment
  }
}
    ${ProductInProductListFragment}`;