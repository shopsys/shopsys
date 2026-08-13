// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleArticleInterfaceFragment } from '../../articlesInterface/fragments/SimpleArticleInterfaceFragment.generated';
import { ListedBrandFragment } from '../../brands/fragments/ListedBrandFragment.generated';
import { ListedCategoryConnectionFragment } from '../../categories/fragments/ListedCategoryConnectionFragment.generated';
import { ProductFilterOptionsFragment } from '../../productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import { ListedProductConnectionFragment } from '../../products/fragments/ListedProductConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Product Availability statuses */
export type TypeAvailabilityStatusEnum =
  /** Product availability status for electronically delivered products */
  | 'Digital'
  /** Product is out of stock with a known expected restocking date */
  | 'ExpectedRestock'
  /** Product availability status in stock */
  | 'InStock'
  /** Product availability status out of stock */
  | 'OutOfStock';

/** Represents a parameter filter */
export type TypeParameterFilter = {
  /** The parameter maximal value (for parameters with "slider" type) */
  maximalValue?: number | null | undefined;
  /** The parameter minimal value (for parameters with "slider" type) */
  minimalValue?: number | null | undefined;
  /** Uuid of filtered parameter */
  parameter: string;
  /** Array of uuids representing parameter values to be filtered by */
  values: Array<string>;
};

/** Represents a product filter */
export type TypeProductFilter = {
  /** Array of uuids of brands filter */
  brands?: Array<string> | null | undefined;
  /** Array of uuids of flags filter */
  flags?: Array<string> | null | undefined;
  /** Maximal price filter */
  maximalPrice?: string | null | undefined;
  /** Minimal price filter */
  minimalPrice?: string | null | undefined;
  /** Only in stock filter */
  onlyInStock?: boolean | null | undefined;
  /** Parameter filter */
  parameters?: Array<TypeParameterFilter> | null | undefined;
};

/** One of possible ordering modes for product */
export type TypeProductOrderingModeEnum =
  /** Order by name ascending */
  | 'NAME_ASC'
  /** Order by name descending */
  | 'NAME_DESC'
  /** Order by price ascending */
  | 'PRICE_ASC'
  /** Order by price descending */
  | 'PRICE_DESC'
  /** Order by priority */
  | 'PRIORITY'
  /** Order by relevance */
  | 'RELEVANCE';

/** One of possible product types */
export type TypeProductTypeEnum =
  /** Basic product */
  | 'BASIC'
  /** Gift voucher delivered by email after the order is paid */
  | 'ELECTRONIC_GIFT_VOUCHER'
  /** Product with inquiry form instead of add to cart button */
  | 'INQUIRY'
  /** Gift voucher delivered printed as a regular product */
  | 'PRINTED_GIFT_VOUCHER';

export type TypeSearchQueryVariables = Exact<{
  search: string;
  isAutocomplete: boolean;
  userIdentifier: string;
  endCursor: string;
  orderingMode?: Types.TypeProductOrderingModeEnum | null | undefined;
  filter?: Types.TypeProductFilter | null | undefined;
  pageSize?: number | null | undefined;
  parameters?: Array<string> | string | null | undefined;
}>;


export type TypeSearchQuery = { articlesSearch: Array<
    | { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean }
    | { __typename: 'BlogArticle', name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
  >, brandSearch: Array<{ __typename: 'Brand', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }>, categoriesSearch: { __typename: 'CategoryConnection', totalCount: number, edges: Array<{ __typename: 'CategoryEdge', node: { __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } } | null } | null> | null }, productsSearch: { __typename: 'ProductConnection', orderingMode: Types.TypeProductOrderingModeEnum, defaultOrderingMode: Types.TypeProductOrderingModeEnum | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<
        | { __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> }
        | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean, colorIcon: { url: string, anchorText: string } | null }> }
        | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }
      > | null }, edges: Array<{ __typename: 'ProductEdge', node:
        | { __typename: 'MainVariant', imagesCount: number, variantsCount: number, id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, productType: Types.TypeProductTypeEnum, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }> }
        | { __typename: 'RegularProduct', imagesCount: number, id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, productType: Types.TypeProductTypeEnum, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }> }
        | { __typename: 'Variant', imagesCount: number, id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, productType: Types.TypeProductTypeEnum, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }> }
       | null } | null> | null, pageInfo: { hasNextPage: boolean } } };


export const SearchQueryDocument = gql`
    query SearchQuery($search: String!, $isAutocomplete: Boolean!, $userIdentifier: Uuid!, $endCursor: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter, $pageSize: Int, $parameters: [Uuid!] = []) {
  articlesSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier, parameters: $parameters}
  ) {
    ...SimpleArticleInterfaceFragment
  }
  brandSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier, parameters: $parameters}
  ) {
    ...ListedBrandFragment
  }
  categoriesSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier, parameters: $parameters}
  ) {
    ...ListedCategoryConnectionFragment
  }
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
    edges {
      node {
        imagesCount
      }
    }
  }
}
    ${SimpleArticleInterfaceFragment}
${ListedBrandFragment}
${ListedCategoryConnectionFragment}
${ProductFilterOptionsFragment}
${ListedProductConnectionFragment}`;

export function useSearchQuery(options: Omit<Urql.UseQueryArgs<TypeSearchQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeSearchQuery, TypeSearchQueryVariables>({ query: SearchQueryDocument, ...options });
};