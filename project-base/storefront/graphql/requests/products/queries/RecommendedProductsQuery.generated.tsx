// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedProductFragment } from '../fragments/ListedProductFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Product Availability statuses */
export type TypeAvailabilityStatusEnum =
  /** Product is out of stock with a known expected restocking date */
  | 'ExpectedRestock'
  /** Product availability status in stock */
  | 'InStock'
  /** Product availability status out of stock */
  | 'OutOfStock';

export type TypeRecommendationType =
  | 'basket'
  | 'basket_popup'
  | 'category'
  | 'item_detail'
  | 'personalized';

export type TypeRecommendedProductsQueryVariables = Exact<{
  userIdentifier: string;
  recommendationType: Types.TypeRecommendationType;
  recommenderClientIdentifier?: string | null | undefined;
  limit?: number | null | undefined;
  itemUuids?: Array<string> | string | null | undefined;
}>;


export type TypeRecommendedProductsQuery = { recommendedProducts: Array<
    | { __typename: 'MainVariant', variantsCount: number, id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null }
    | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null }
    | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, mainVariant: { __typename: 'MainVariant', reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null } | null, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null }
  > };


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