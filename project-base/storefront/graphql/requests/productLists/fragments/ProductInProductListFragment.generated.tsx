// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedProductFragment } from '../../products/fragments/ListedProductFragment.generated';
import { ParameterFragment } from '../../parameters/fragments/ParameterFragment.generated';
export type TypeProductInProductListFragment_MainVariant_ = (
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
    & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'percentageDiscount'>
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

export type TypeProductInProductListFragment_RegularProduct_ = (
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
    & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'percentageDiscount'>
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

export type TypeProductInProductListFragment_Variant_ = (
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
    & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'percentageDiscount'>
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

export type TypeProductInProductListFragment = TypeProductInProductListFragment_MainVariant_ | TypeProductInProductListFragment_RegularProduct_ | TypeProductInProductListFragment_Variant_;

export const ProductInProductListFragment = gql`
    fragment ProductInProductListFragment on Product {
  ...ListedProductFragment
  parameters {
    ...ParameterFragment
  }
}
    ${ListedProductFragment}
${ParameterFragment}`;