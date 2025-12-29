// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductListFragment } from '../fragments/ProductListFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeProductListQueryVariables = Types.Exact<{
  input: Types.TypeProductListInput;
}>;


export type TypeProductListQuery = { __typename?: 'Query', productList: { __typename: 'ProductList', uuid: string, products: Array<{ __typename: 'MainVariant', variantsCount: number, id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, unit: { __typename?: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, unit: { __typename?: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, mainVariant: { __typename?: 'MainVariant', slug: string } | null, unit: { __typename?: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> } | null };


export const ProductListQueryDocument = gql`
    query ProductListQuery($input: ProductListInput!) {
  productList(input: $input) {
    ...ProductListFragment
  }
}
    ${ProductListFragment}`;

export function useProductListQuery(options: Omit<Urql.UseQueryArgs<TypeProductListQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeProductListQuery, TypeProductListQueryVariables>({ query: ProductListQueryDocument, ...options });
};