// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedProductFragment } from './ListedProductFragment.generated';
export type TypeListedProductConnectionFragment = (
  { __typename: 'ProductConnection' }
  & { pageInfo: (
    { __typename?: 'PageInfo' }
    & Pick<Types.TypePageInfo, 'hasNextPage'>
  ), edges: Types.Maybe<Array<Types.Maybe<(
    { __typename: 'ProductEdge' }
    & { node: Types.Maybe<(
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
    ) | (
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
    ) | (
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
    )> }
  )>>> }
);

export const ListedProductConnectionFragment = gql`
    fragment ListedProductConnectionFragment on ProductConnection {
  __typename
  pageInfo {
    hasNextPage
  }
  edges {
    __typename
    node {
      ...ListedProductFragment
    }
  }
}
    ${ListedProductFragment}`;