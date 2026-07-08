// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedProductConnectionFragment } from '../fragments/ListedProductConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBrandProductsQueryVariables = Types.Exact<{
  endCursor: Types.Scalars['String']['input'];
  orderingMode?: Types.InputMaybe<Types.TypeProductOrderingModeEnum>;
  filter?: Types.InputMaybe<Types.TypeProductFilter>;
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
  pageSize?: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;


export type TypeBrandProductsQuery = (
  { __typename?: 'Query' }
  & { products: (
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
    )>>> }
  ) }
);


export const BrandProductsQueryDocument = gql`
    query BrandProductsQuery($endCursor: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter, $urlSlug: String, $pageSize: Int) {
  products(
    brandSlug: $urlSlug
    after: $endCursor
    orderingMode: $orderingMode
    filter: $filter
    first: $pageSize
  ) {
    ...ListedProductConnectionFragment
  }
}
    ${ListedProductConnectionFragment}`;

export function useBrandProductsQuery(options: Omit<Urql.UseQueryArgs<TypeBrandProductsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeBrandProductsQuery, TypeBrandProductsQueryVariables>({ query: BrandProductsQueryDocument, ...options });
};