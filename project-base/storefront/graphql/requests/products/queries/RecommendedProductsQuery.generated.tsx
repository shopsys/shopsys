// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedProductFragment } from '../fragments/ListedProductFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeRecommendedProductsQueryVariables = Types.Exact<{
  userIdentifier: Types.Scalars['Uuid']['input'];
  recommendationType: Types.TypeRecommendationType;
  recommenderClientIdentifier?: Types.InputMaybe<Types.Scalars['String']['input']>;
  limit?: Types.InputMaybe<Types.Scalars['Int']['input']>;
  itemUuids?: Types.InputMaybe<Array<Types.Scalars['Uuid']['input']> | Types.Scalars['Uuid']['input']>;
}>;


export type TypeRecommendedProductsQuery = (
  { __typename?: 'Query' }
  & { recommendedProducts: Array<(
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


export const RecommendedProductsQueryDocument = gql`
    query RecommendedProductsQuery($userIdentifier: Uuid!, $recommendationType: RecommendationType!, $recommenderClientIdentifier: String, $limit: Int, $itemUuids: [Uuid!]) {
  recommendedProducts(
    userIdentifier: $userIdentifier
    recommendationType: $recommendationType
    recommenderClientIdentifier: $recommenderClientIdentifier
    limit: $limit
    itemUuids: $itemUuids
  ) {
    ...ListedProductFragment
  }
}
    ${ListedProductFragment}`;

export function useRecommendedProductsQuery(options: Omit<Urql.UseQueryArgs<TypeRecommendedProductsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeRecommendedProductsQuery, TypeRecommendedProductsQueryVariables>({ query: RecommendedProductsQueryDocument, ...options });
};