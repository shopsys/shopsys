// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleFlagFragment } from '../../flags/fragments/SimpleFlagFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { AvailabilityFragment } from '../../availabilities/fragments/AvailabilityFragment.generated';
import { ProductPriceFragment } from '../../products/fragments/ProductPriceFragment.generated';
import { SimpleBrandFragment } from '../../brands/fragments/SimpleBrandFragment.generated';
import { ParameterFragment } from '../../parameters/fragments/ParameterFragment.generated';
export type TypeCartItemFragment = (
  { __typename: 'CartItem' }
  & Pick<Types.TypeCartItem, 'uuid' | 'quantity' | 'type' | 'freeQuantity'>
  & { product: (
    { __typename: 'MainVariant' }
    & Pick<Types.TypeMainVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
    & { flags: Array<(
      { __typename: 'Flag' }
      & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
    )>, mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, availability: (
      { __typename: 'Availability' }
      & Pick<Types.TypeAvailability, 'name' | 'status'>
    ), price: (
      { __typename: 'ProductPrice' }
      & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
      & { basicPrice: (
        { __typename?: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
      ) }
    ), giftPrice: (
      { __typename: 'ProductPrice' }
      & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
      & { basicPrice: (
        { __typename?: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
      ) }
    ), unit: (
      { __typename?: 'Unit' }
      & Pick<Types.TypeUnit, 'name'>
    ), brand: Types.Maybe<(
      { __typename: 'Brand' }
      & Pick<Types.TypeBrand, 'name' | 'slug'>
    )>, categories: Array<(
      { __typename?: 'Category' }
      & Pick<Types.TypeCategory, 'name'>
    )>, parameters: Array<(
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
    )> }
  ) | (
    { __typename: 'RegularProduct' }
    & Pick<Types.TypeRegularProduct, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
    & { flags: Array<(
      { __typename: 'Flag' }
      & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
    )>, mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, availability: (
      { __typename: 'Availability' }
      & Pick<Types.TypeAvailability, 'name' | 'status'>
    ), price: (
      { __typename: 'ProductPrice' }
      & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
      & { basicPrice: (
        { __typename?: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
      ) }
    ), giftPrice: (
      { __typename: 'ProductPrice' }
      & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
      & { basicPrice: (
        { __typename?: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
      ) }
    ), unit: (
      { __typename?: 'Unit' }
      & Pick<Types.TypeUnit, 'name'>
    ), brand: Types.Maybe<(
      { __typename: 'Brand' }
      & Pick<Types.TypeBrand, 'name' | 'slug'>
    )>, categories: Array<(
      { __typename?: 'Category' }
      & Pick<Types.TypeCategory, 'name'>
    )>, parameters: Array<(
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
    )> }
  ) | (
    { __typename: 'Variant' }
    & Pick<Types.TypeVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
    & { mainVariant: Types.Maybe<(
      { __typename?: 'MainVariant' }
      & Pick<Types.TypeMainVariant, 'slug'>
    )>, flags: Array<(
      { __typename: 'Flag' }
      & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
    )>, mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, availability: (
      { __typename: 'Availability' }
      & Pick<Types.TypeAvailability, 'name' | 'status'>
    ), price: (
      { __typename: 'ProductPrice' }
      & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
      & { basicPrice: (
        { __typename?: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
      ) }
    ), giftPrice: (
      { __typename: 'ProductPrice' }
      & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
      & { basicPrice: (
        { __typename?: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
      ) }
    ), unit: (
      { __typename?: 'Unit' }
      & Pick<Types.TypeUnit, 'name'>
    ), brand: Types.Maybe<(
      { __typename: 'Brand' }
      & Pick<Types.TypeBrand, 'name' | 'slug'>
    )>, categories: Array<(
      { __typename?: 'Category' }
      & Pick<Types.TypeCategory, 'name'>
    )>, parameters: Array<(
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
    )> }
  ) }
);

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