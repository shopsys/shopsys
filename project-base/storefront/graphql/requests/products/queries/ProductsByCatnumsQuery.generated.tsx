// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedProductFragment } from '../fragments/ListedProductFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeProductsByCatnumsVariables = Types.Exact<{
  catnums: Array<Types.Scalars['String']['input']> | Types.Scalars['String']['input'];
}>;


export type TypeProductsByCatnums = (
  { __typename?: 'Query' }
  & { productsByCatnums: Array<(
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
  )> }
);


export const ProductsByCatnumsDocument = gql`
    query ProductsByCatnums($catnums: [String!]!) {
  productsByCatnums(catnums: $catnums) {
    ...ListedProductFragment
  }
}
    ${ListedProductFragment}`;

export function useProductsByCatnums(options: Omit<Urql.UseQueryArgs<TypeProductsByCatnumsVariables>, 'query'>) {
  return Urql.useQuery<TypeProductsByCatnums, TypeProductsByCatnumsVariables>({ query: ProductsByCatnumsDocument, ...options });
};