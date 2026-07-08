// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductFilterOptionsFragment } from '../../productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import { ListedProductConnectionFragment } from '../../products/fragments/ListedProductConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSearchProductsQueryVariables = Types.Exact<{
  endCursor: Types.Scalars['String']['input'];
  orderingMode?: Types.InputMaybe<Types.TypeProductOrderingModeEnum>;
  filter?: Types.InputMaybe<Types.TypeProductFilter>;
  search: Types.Scalars['String']['input'];
  pageSize?: Types.InputMaybe<Types.Scalars['Int']['input']>;
  isAutocomplete: Types.Scalars['Boolean']['input'];
  userIdentifier: Types.Scalars['Uuid']['input'];
  parameters?: Types.InputMaybe<Array<Types.Scalars['Uuid']['input']> | Types.Scalars['Uuid']['input']>;
}>;


export type TypeSearchProductsQuery = (
  { __typename?: 'Query' }
  & { productsSearch: (
    { __typename: 'ProductConnection' }
    & Pick<Types.TypeProductConnection, 'orderingMode' | 'defaultOrderingMode' | 'totalCount'>
    & { productFilterOptions: (
      { __typename: 'ProductFilterOptions' }
      & Pick<Types.TypeProductFilterOptions, 'minimalPrice' | 'maximalPrice' | 'inStock'>
      & { brands: Types.Maybe<Array<(
        { __typename: 'BrandFilterOption' }
        & Pick<Types.TypeBrandFilterOption, 'count'>
        & { brand: (
          { __typename: 'Brand' }
          & Pick<Types.TypeBrand, 'uuid' | 'name'>
        ) }
      )>>, flags: Types.Maybe<Array<(
        { __typename: 'FlagFilterOption' }
        & Pick<Types.TypeFlagFilterOption, 'count' | 'isSelected'>
        & { flag: (
          { __typename: 'Flag' }
          & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
        ) }
      )>>, parameters: Types.Maybe<Array<(
        { __typename: 'ParameterCheckboxFilterOption' }
        & Pick<Types.TypeParameterCheckboxFilterOption, 'name' | 'uuid' | 'isCollapsed'>
        & { values: Array<(
          { __typename: 'ParameterValueFilterOption' }
          & Pick<Types.TypeParameterValueFilterOption, 'uuid' | 'text' | 'count' | 'isSelected'>
        )> }
      ) | (
        { __typename: 'ParameterColorFilterOption' }
        & Pick<Types.TypeParameterColorFilterOption, 'name' | 'uuid' | 'isCollapsed'>
        & { values: Array<(
          { __typename: 'ParameterValueColorFilterOption' }
          & Pick<Types.TypeParameterValueColorFilterOption, 'uuid' | 'text' | 'count' | 'rgbHex' | 'isSelected'>
          & { colorIcon: Types.Maybe<(
            { __typename?: 'File' }
            & Pick<Types.TypeFile, 'url' | 'anchorText'>
          )> }
        )> }
      ) | (
        { __typename: 'ParameterSliderFilterOption' }
        & Pick<Types.TypeParameterSliderFilterOption, 'name' | 'uuid' | 'minimalValue' | 'maximalValue' | 'isCollapsed' | 'selectedValue' | 'isSelectable'>
        & { unit: Types.Maybe<(
          { __typename: 'Unit' }
          & Pick<Types.TypeUnit, 'name'>
        )> }
      )>> }
    ), pageInfo: (
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


export const SearchProductsQueryDocument = gql`
    query SearchProductsQuery($endCursor: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter, $search: String!, $pageSize: Int, $isAutocomplete: Boolean!, $userIdentifier: Uuid!, $parameters: [Uuid!] = []) {
  productsSearch(
    after: $endCursor
    orderingMode: $orderingMode
    filter: $filter
    first: $pageSize
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier, parameters: $parameters}
  ) {
    orderingMode
    defaultOrderingMode
    totalCount
    productFilterOptions {
      ...ProductFilterOptionsFragment
    }
    ...ListedProductConnectionFragment
  }
}
    ${ProductFilterOptionsFragment}
${ListedProductConnectionFragment}`;

export function useSearchProductsQuery(options: Omit<Urql.UseQueryArgs<TypeSearchProductsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeSearchProductsQuery, TypeSearchProductsQueryVariables>({ query: SearchProductsQueryDocument, ...options });
};