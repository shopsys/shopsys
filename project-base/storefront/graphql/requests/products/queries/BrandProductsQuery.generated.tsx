// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedProductConnectionFragment } from '../fragments/ListedProductConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Product Availability statuses */
export type TypeAvailabilityStatusEnum =
  /** Product availability status for electronically delivered products */
  | 'Digital'
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

export type TypeBrandProductsQueryVariables = Exact<{
  endCursor: string;
  orderingMode?: Types.TypeProductOrderingModeEnum | null | undefined;
  filter?: Types.TypeProductFilter | null | undefined;
  urlSlug?: string | null | undefined;
  pageSize?: number | null | undefined;
}>;


export type TypeBrandProductsQuery = { products: { __typename: 'ProductConnection', edges: Array<{ __typename: 'ProductEdge', node:
        | { __typename: 'MainVariant', imagesCount: number, variantsCount: number, id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, productType: Types.TypeProductTypeEnum, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }> }
        | { __typename: 'RegularProduct', imagesCount: number, id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, productType: Types.TypeProductTypeEnum, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }> }
        | { __typename: 'Variant', imagesCount: number, id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, productType: Types.TypeProductTypeEnum, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }> }
       | null } | null> | null, pageInfo: { hasNextPage: boolean } } };


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
    edges {
      node {
        imagesCount
      }
    }
  }
}
    ${ListedProductConnectionFragment}`;

export function useBrandProductsQuery(options: Omit<Urql.UseQueryArgs<TypeBrandProductsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeBrandProductsQuery, TypeBrandProductsQueryVariables>({ query: BrandProductsQueryDocument, ...options });
};