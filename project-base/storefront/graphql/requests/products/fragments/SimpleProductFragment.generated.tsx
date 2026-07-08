// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductPriceFragment } from './ProductPriceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { SimpleBrandFragment } from '../../brands/fragments/SimpleBrandFragment.generated';
import { SimpleFlagFragment } from '../../flags/fragments/SimpleFlagFragment.generated';
import { AvailabilityFragment } from '../../availabilities/fragments/AvailabilityFragment.generated';
export type TypeSimpleProductFragment_MainVariant_ = (
  { __typename: 'MainVariant' }
  & Pick<Types.TypeMainVariant, 'id' | 'uuid' | 'catalogNumber' | 'fullName' | 'slug'>
  & { price: (
    { __typename: 'ProductPrice' }
    & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
    & { basicPrice: (
      { __typename?: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
    ) }
  ), mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, unit: (
    { __typename?: 'Unit' }
    & Pick<Types.TypeUnit, 'name'>
  ), brand: Types.Maybe<(
    { __typename: 'Brand' }
    & Pick<Types.TypeBrand, 'name' | 'slug'>
  )>, categories: Array<(
    { __typename?: 'Category' }
    & Pick<Types.TypeCategory, 'name'>
  )>, flags: Array<(
    { __typename: 'Flag' }
    & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
  )>, availability: (
    { __typename: 'Availability' }
    & Pick<Types.TypeAvailability, 'name' | 'status'>
  ) }
);

export type TypeSimpleProductFragment_RegularProduct_ = (
  { __typename: 'RegularProduct' }
  & Pick<Types.TypeRegularProduct, 'id' | 'uuid' | 'catalogNumber' | 'fullName' | 'slug'>
  & { price: (
    { __typename: 'ProductPrice' }
    & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
    & { basicPrice: (
      { __typename?: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
    ) }
  ), mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, unit: (
    { __typename?: 'Unit' }
    & Pick<Types.TypeUnit, 'name'>
  ), brand: Types.Maybe<(
    { __typename: 'Brand' }
    & Pick<Types.TypeBrand, 'name' | 'slug'>
  )>, categories: Array<(
    { __typename?: 'Category' }
    & Pick<Types.TypeCategory, 'name'>
  )>, flags: Array<(
    { __typename: 'Flag' }
    & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
  )>, availability: (
    { __typename: 'Availability' }
    & Pick<Types.TypeAvailability, 'name' | 'status'>
  ) }
);

export type TypeSimpleProductFragment_Variant_ = (
  { __typename: 'Variant' }
  & Pick<Types.TypeVariant, 'id' | 'uuid' | 'catalogNumber' | 'fullName' | 'slug'>
  & { price: (
    { __typename: 'ProductPrice' }
    & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
    & { basicPrice: (
      { __typename?: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
    ) }
  ), mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, unit: (
    { __typename?: 'Unit' }
    & Pick<Types.TypeUnit, 'name'>
  ), brand: Types.Maybe<(
    { __typename: 'Brand' }
    & Pick<Types.TypeBrand, 'name' | 'slug'>
  )>, categories: Array<(
    { __typename?: 'Category' }
    & Pick<Types.TypeCategory, 'name'>
  )>, flags: Array<(
    { __typename: 'Flag' }
    & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
  )>, availability: (
    { __typename: 'Availability' }
    & Pick<Types.TypeAvailability, 'name' | 'status'>
  ) }
);

export type TypeSimpleProductFragment = TypeSimpleProductFragment_MainVariant_ | TypeSimpleProductFragment_RegularProduct_ | TypeSimpleProductFragment_Variant_;

export const SimpleProductFragment = gql`
    fragment SimpleProductFragment on Product {
  __typename
  id
  uuid
  catalogNumber
  fullName
  slug
  price {
    ...ProductPriceFragment
  }
  mainImage {
    ...ImageFragment
  }
  unit {
    name
  }
  brand {
    ...SimpleBrandFragment
  }
  categories {
    name
  }
  flags {
    ...SimpleFlagFragment
  }
  availability {
    ...AvailabilityFragment
  }
}
    ${ProductPriceFragment}
${ImageFragment}
${SimpleBrandFragment}
${SimpleFlagFragment}
${AvailabilityFragment}`;