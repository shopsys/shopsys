// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CategoryDetailFragment } from '../fragments/CategoryDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCategoryDetailQueryVariables = Types.Exact<{
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
  orderingMode?: Types.InputMaybe<Types.TypeProductOrderingModeEnum>;
  filter?: Types.InputMaybe<Types.TypeProductFilter>;
}>;


export type TypeCategoryDetailQuery = (
  { __typename?: 'Query' }
  & { category: Types.Maybe<(
    { __typename: 'Category' }
    & Pick<Types.TypeCategory, 'id' | 'uuid' | 'slug' | 'originalCategorySlug' | 'zboziCategory' | 'name' | 'description' | 'seoH1' | 'seoTitle' | 'seoMetaDescription' | 'automatedFilters'>
    & { images: Array<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, readyCategorySeoMixLinks: Array<(
      { __typename: 'Link' }
      & Pick<Types.TypeLink, 'name' | 'slug'>
    )>, hreflangLinks: Array<(
      { __typename?: 'HreflangLink' }
      & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
    )>, breadcrumb: Array<(
      { __typename: 'Link' }
      & Pick<Types.TypeLink, 'name' | 'slug'>
    )>, categoryHierarchy: Array<(
      { __typename?: 'CategoryHierarchyItem' }
      & Pick<Types.TypeCategoryHierarchyItem, 'id' | 'name'>
    )>, children: Array<(
      { __typename: 'Category' }
      & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
      & { mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, products: (
        { __typename: 'ProductConnection' }
        & Pick<Types.TypeProductConnection, 'totalCount'>
      ) }
    )>, products: (
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
      ) }
    ), bestsellers: Array<(
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
      & { mainVariant: Types.Maybe<(
        { __typename?: 'MainVariant' }
        & Pick<Types.TypeMainVariant, 'slug'>
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
    )> }
  )> }
);


export const CategoryDetailQueryDocument = gql`
    query CategoryDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) @friendlyUrl {
  category(urlSlug: $urlSlug, orderingMode: $orderingMode, filter: $filter) {
    ...CategoryDetailFragment
  }
}
    ${CategoryDetailFragment}`;

export function useCategoryDetailQuery(options?: Omit<Urql.UseQueryArgs<TypeCategoryDetailQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCategoryDetailQuery, TypeCategoryDetailQueryVariables>({ query: CategoryDetailQueryDocument, ...options });
};