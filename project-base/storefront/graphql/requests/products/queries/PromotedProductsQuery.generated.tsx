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

export type TypePromotedProductsQueryVariables = Exact<{ [key: string]: never; }>;


export type TypePromotedProductsQuery = { promotedProducts: Array<
    | { __typename: 'MainVariant', variantsCount: number, id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, isPersonalPickupOnly: boolean, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }> }
    | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, isPersonalPickupOnly: boolean, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }> }
    | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, isPersonalPickupOnly: boolean, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }> }
  > };


export const PromotedProductsQueryDocument = gql`
    query PromotedProductsQuery @redisCachePerPricingGroup(ttl: 3600) {
  promotedProducts {
    ...ListedProductFragment
  }
}
    ${ListedProductFragment}`;

export function usePromotedProductsQuery(options?: Omit<Urql.UseQueryArgs<TypePromotedProductsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypePromotedProductsQuery, TypePromotedProductsQueryVariables>({ query: PromotedProductsQueryDocument, ...options });
};