// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedProductFragment } from '../fragments/ListedProductFragment.generated';
import { AdditionalServiceFragment } from '../../additionalServices/fragments/AdditionalServiceFragment.generated';
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

export type TypeProductVariantsQueryVariables = Exact<{
  uuid: string;
}>;


export type TypeProductVariantsQuery = { product:
    | { __typename: 'MainVariant', variants: Array<{ __typename: 'Variant', stockQuantity: number | null, isAllowedNegativeStock: boolean, isCurrentlyOutOfStock: boolean, availableStoresCount: number | null, isPersonalPickupOnly: boolean, isInquiryType: boolean, id: number, uuid: string, slug: string, fullName: string, isSellingDenied: boolean, expectedRestockingDate: string | null, catalogNumber: string, isMainVariant: boolean, productType: Types.TypeProductTypeEnum, additionalServices: Array<{ __typename: 'AdditionalService', id: number, uuid: string, name: string, catnum: string, description: string | null, deliveryDaysExtension: number | null, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null }>, unit: { __typename: 'Unit', name: string }, mainVariant: { __typename: 'MainVariant', reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null } | null, mainCategory: { name: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }>, reviewsSummary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number } | null }> }
    | { __typename: 'RegularProduct' }
    | { __typename: 'Variant' }
   | null };


export const ProductVariantsQueryDocument = gql`
    query ProductVariantsQuery($uuid: Uuid!) {
  product(uuid: $uuid) {
    __typename
    ... on MainVariant {
      variants {
        ...ListedProductFragment
        additionalServices {
          ...AdditionalServiceFragment
        }
      }
    }
  }
}
    ${ListedProductFragment}
${AdditionalServiceFragment}`;

export function useProductVariantsQuery(options: Omit<Urql.UseQueryArgs<TypeProductVariantsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeProductVariantsQuery, TypeProductVariantsQueryVariables>({ query: ProductVariantsQueryDocument, ...options });
};