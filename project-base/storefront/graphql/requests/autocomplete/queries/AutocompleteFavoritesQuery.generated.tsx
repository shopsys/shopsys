// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedProductFragment } from '../../products/fragments/ListedProductFragment.generated';
import { SimpleCategoryFragment } from '../../categories/fragments/SimpleCategoryFragment.generated';
import { SimpleBrandFragment } from '../../brands/fragments/SimpleBrandFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeAutocompleteFavoritesQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeAutocompleteFavoritesQuery = (
  { __typename?: 'Query' }
  & { autocompleteFavorites: (
    { __typename?: 'AutocompleteFavorites' }
    & { products: Array<(
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
    )>, categories: Array<(
      { __typename: 'Category' }
      & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
    )>, brands: Array<(
      { __typename: 'Brand' }
      & Pick<Types.TypeBrand, 'name' | 'slug'>
    )> }
  ) }
);


export const AutocompleteFavoritesQueryDocument = gql`
    query AutocompleteFavoritesQuery {
  autocompleteFavorites {
    products {
      ...ListedProductFragment
    }
    categories {
      ...SimpleCategoryFragment
    }
    brands {
      ...SimpleBrandFragment
    }
  }
}
    ${ListedProductFragment}
${SimpleCategoryFragment}
${SimpleBrandFragment}`;

export function useAutocompleteFavoritesQuery(options?: Omit<Urql.UseQueryArgs<TypeAutocompleteFavoritesQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeAutocompleteFavoritesQuery, TypeAutocompleteFavoritesQueryVariables>({ query: AutocompleteFavoritesQueryDocument, ...options });
};