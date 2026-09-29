// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleArticleInterfaceFragment } from '../../articlesInterface/fragments/SimpleArticleInterfaceFragment.generated';
import { SimpleBrandFragment } from '../../brands/fragments/SimpleBrandFragment.generated';
import { SimpleCategoryFragment } from '../../categories/fragments/SimpleCategoryFragment.generated';
import { CompactProductFragment } from '../../products/fragments/CompactProductFragment.generated';
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

export type TypeAutocompleteSearchQueryVariables = Exact<{
  search: string;
  maxProductCount?: number | null | undefined;
  maxCategoryCount?: number | null | undefined;
  isAutocomplete: boolean;
  userIdentifier: string;
}>;


export type TypeAutocompleteSearchQuery = { articlesSearch: Array<
    | { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean }
    | { __typename: 'BlogArticle', name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
  >, brandSearch: Array<{ __typename: 'Brand', name: string, slug: string }>, categoriesSearch: { __typename: 'CategoryConnection', totalCount: number, edges: Array<{ __typename: 'CategoryEdge', node: { __typename: 'Category', uuid: string, name: string, slug: string } | null } | null> | null }, productsSearch: { __typename: 'ProductConnection', totalCount: number, edges: Array<{ __typename: 'ProductEdge', node:
        | { __typename: 'MainVariant', variantsCount: number, id: number, uuid: string, slug: string, fullName: string, isSellingDenied: boolean, expectedRestockingDate: string | null, catalogNumber: string, isMainVariant: boolean, productType: Types.TypeProductTypeEnum, mainCategory: { name: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null }
        | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, isSellingDenied: boolean, expectedRestockingDate: string | null, catalogNumber: string, isMainVariant: boolean, productType: Types.TypeProductTypeEnum, mainCategory: { name: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null }
        | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, isSellingDenied: boolean, expectedRestockingDate: string | null, catalogNumber: string, isMainVariant: boolean, productType: Types.TypeProductTypeEnum, mainVariant: { __typename: 'MainVariant', reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null } | null, mainCategory: { name: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null }
       | null } | null> | null } };


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
    __typename
    totalCount
    edges {
      __typename
      node {
        ...SimpleCategoryFragment
      }
    }
  }
  productsSearch: productsSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
    first: $maxProductCount
  ) {
    __typename
    totalCount
    edges {
      __typename
      node {
        ...CompactProductFragment
      }
    }
  }
}
    ${SimpleArticleInterfaceFragment}
${SimpleBrandFragment}
${SimpleCategoryFragment}
${CompactProductFragment}`;

export function useAutocompleteSearchQuery(options: Omit<Urql.UseQueryArgs<TypeAutocompleteSearchQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeAutocompleteSearchQuery, TypeAutocompleteSearchQueryVariables>({ query: AutocompleteSearchQueryDocument, ...options });
};