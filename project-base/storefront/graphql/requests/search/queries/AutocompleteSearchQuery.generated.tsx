import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleArticleInterfaceFragment } from '../../articlesInterface/fragments/SimpleArticleInterfaceFragment.generated';
import { SimpleBrandFragment } from '../../brands/fragments/SimpleBrandFragment.generated';
import { SimpleCategoryConnectionFragment } from '../../categories/fragments/SimpleCategoryConnectionFragment.generated';
import { ProductFilterOptionsFragment } from '../../productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import { ListedProductConnectionFragment } from '../../products/fragments/ListedProductConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeAutocompleteSearchQueryVariables = Types.Exact<{
  search: Types.Scalars['String']['input'];
  maxProductCount: Types.InputMaybe<Types.Scalars['Int']['input']>;
  maxCategoryCount: Types.InputMaybe<Types.Scalars['Int']['input']>;
  isAutocomplete: Types.Scalars['Boolean']['input'];
  userIdentifier: Types.Scalars['Uuid']['input'];
}>;


export type TypeAutocompleteSearchQuery = { __typename?: 'Query', articlesSearch: Array<{ __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean } | { __typename: 'BlogArticle', name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }>, brandSearch: Array<{ __typename: 'Brand', name: string, slug: string }>, categoriesSearch: { __typename: 'CategoryConnection', totalCount: number, edges: Array<{ __typename: 'CategoryEdge', node: { __typename: 'Category', uuid: string, name: string, slug: string } | null } | null> | null }, productsSearch: { __typename: 'ProductConnection', orderingMode: Types.TypeProductOrderingModeEnum, defaultOrderingMode: Types.TypeProductOrderingModeEnum | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean }, edges: Array<{ __typename: 'ProductEdge', node: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | null } | null> | null } };


      export interface PossibleTypesResultData {
        possibleTypes: {
          [key: string]: string[]
        }
      }
      const result: PossibleTypesResultData = {
  "possibleTypes": {
    "Advert": [
      "AdvertCode",
      "AdvertImage"
    ],
    "ArticleInterface": [
      "ArticleSite",
      "BlogArticle"
    ],
    "Breadcrumb": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ],
    "CartInterface": [
      "Cart"
    ],
    "CustomerUser": [
      "CompanyCustomerUser",
      "RegularCustomerUser"
    ],
    "Hreflang": [
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "SeoPage",
      "Variant"
    ],
    "NotBlogArticleInterface": [
      "ArticleLink",
      "ArticleSite"
    ],
    "ParameterFilterOptionInterface": [
      "ParameterCheckboxFilterOption",
      "ParameterColorFilterOption",
      "ParameterSliderFilterOption"
    ],
    "PriceInterface": [
      "Price",
      "ProductPrice"
    ],
    "Product": [
      "MainVariant",
      "RegularProduct",
      "Variant"
    ],
    "ProductListable": [
      "Brand",
      "Category",
      "Flag"
    ],
    "Slug": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ]
  }
};
      export default result;
    

export const AutocompleteSearchQueryDocument = gql`
    query AutocompleteSearchQuery($search: String!, $maxProductCount: Int, $maxCategoryCount: Int, $isAutocomplete: Boolean!, $userIdentifier: Uuid!) {
  articlesSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
  ) {
    ...SimpleArticleInterfaceFragment
  }
  brandSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
  ) {
    ...SimpleBrandFragment
  }
  categoriesSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
    first: $maxCategoryCount
  ) {
    ...SimpleCategoryConnectionFragment
  }
  productsSearch: productsSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
    first: $maxProductCount
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
    ${SimpleArticleInterfaceFragment}
${SimpleBrandFragment}
${SimpleCategoryConnectionFragment}
${ProductFilterOptionsFragment}
${ListedProductConnectionFragment}`;

export function useAutocompleteSearchQuery(options: Omit<Urql.UseQueryArgs<TypeAutocompleteSearchQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeAutocompleteSearchQuery, TypeAutocompleteSearchQueryVariables>({ query: AutocompleteSearchQueryDocument, ...options });
};