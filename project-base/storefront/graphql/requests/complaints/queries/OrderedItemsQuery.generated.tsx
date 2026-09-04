// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ComplaintOrderedItemFragment } from '../fragments/ComplaintOrderedItemFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** One of possible types of the order item */
export type TypeOrderItemTypeEnum =
  | 'discount'
  | 'payment'
  | 'product'
  | 'productGift'
  | 'promotion'
  | 'rounding'
  | 'transport';

/** Filter order items */
export type TypeOrderItemsFilterInput = {
  /** Filter order items by product catalog number (OR condition with productUuid) */
  catnum?: string | null | undefined;
  /** Exclude order items of products with these product types */
  excludeProductTypes?: Array<TypeProductTypeEnum> | null | undefined;
  /** Filter order items in orders created after this date */
  orderCreatedAfter?: string | null | undefined;
  /** Filter orders created after this date */
  orderStatus?: TypeOrderStatusEnum | null | undefined;
  /** Filter order items by order with this UUID */
  orderUuid?: string | null | undefined;
  /** Filter order items by product with this UUID (OR condition with catnum) */
  productUuid?: string | null | undefined;
  /** Filter order items by type */
  type?: TypeOrderItemTypeEnum | null | undefined;
};

/** Status of order */
export type TypeOrderStatusEnum =
  /** Canceled */
  | 'canceled'
  /** Done */
  | 'done'
  /** In progress */
  | 'inProgress'
  /** New */
  | 'new'
  /** Withdrawn */
  | 'withdrawn';

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

export type TypeOrderedItemsQueryVariables = Exact<{
  first?: number | null | undefined;
  after?: string | null | undefined;
  filter?: Types.TypeOrderItemsFilterInput | null | undefined;
}>;


export type TypeOrderedItemsQuery = { orderItems: { __typename: 'OrderItemConnection', totalCount: number, edges: Array<{ __typename: 'OrderItemEdge', node: { uuid: string, name: string, quantity: number, unit: string | null, totalPrice: { priceWithVat: string }, order: { uuid: string, number: string, creationDate: string }, product:
          | { isVisible: boolean, slug: string, productType: Types.TypeProductTypeEnum, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
          | { isVisible: boolean, slug: string, productType: Types.TypeProductTypeEnum, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
          | { isVisible: boolean, slug: string, productType: Types.TypeProductTypeEnum, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
         | null } | null } | null> | null } };


export const OrderedItemsQueryDocument = gql`
    query OrderedItemsQuery($first: Int, $after: String, $filter: OrderItemsFilterInput) {
  orderItems(first: $first, after: $after, filter: $filter) {
    __typename
    totalCount
    edges {
      __typename
      node {
        ...ComplaintOrderedItemFragment
      }
    }
  }
}
    ${ComplaintOrderedItemFragment}`;

export function useOrderedItemsQuery(options?: Omit<Urql.UseQueryArgs<TypeOrderedItemsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrderedItemsQuery, TypeOrderedItemsQueryVariables>({ query: OrderedItemsQueryDocument, ...options });
};