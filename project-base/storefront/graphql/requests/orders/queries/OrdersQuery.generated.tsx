// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderListFragment } from '../fragments/OrderListFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Filter orders */
export type TypeOrderFilterInput = {
  /** Filter orders created after this date */
  createdAfter?: string | null | undefined;
  /** Filter orders created before this date */
  createdBefore?: string | null | undefined;
  /** Filter orders by order items with product catalog number (OR condition with orderItemsProductUuid) */
  orderItemsCatnum?: string | null | undefined;
  /** Filter orders by order items with product UUID (OR condition with orderItemsCatnum) */
  orderItemsProductUuid?: string | null | undefined;
  /** Filter orders by order number or product */
  search?: string | null | undefined;
  /** Filter orders by status codes */
  statusCodes?: Array<string> | null | undefined;
};

/** One of possible types of the order item */
export type TypeOrderItemTypeEnum =
  | 'discount'
  | 'payment'
  | 'product'
  | 'productGift'
  | 'promotion'
  | 'rounding'
  | 'transport';

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

export type TypeOrdersQueryVariables = Exact<{
  after?: string | null | undefined;
  filter?: Types.TypeOrderFilterInput | null | undefined;
  first?: number | null | undefined;
  statuslessFilter?: Types.TypeOrderFilterInput | null | undefined;
}>;


export type TypeOrdersQuery = { orders: { __typename: 'OrderConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'OrderEdge', cursor: string, node: { __typename: 'Order', uuid: string, number: string, creationDate: string, isPaid: boolean, hasExternalPayment: boolean, hasPaymentInProcess: boolean, isAwaitingPayment: boolean, status: string, note: string | null, productItems: Array<{ __typename: 'OrderItem', quantity: number, product:
            | { __typename: 'MainVariant', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
            | { __typename: 'RegularProduct', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
            | { __typename: 'Variant', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
           | null }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, items: Array<{ type: Types.TypeOrderItemTypeEnum, payment: { name: string, mainImage: { url: string } | null } | null, transport: { name: string, mainImage: { url: string } | null } | null }> } | null } | null> | null } | null, orderStatusCounts: Array<{ count: number, status: { code: string, type: Types.TypeOrderStatusEnum, name: string } }> };


export const OrdersQueryDocument = gql`
    query OrdersQuery($after: String, $filter: OrderFilterInput, $first: Int, $statuslessFilter: OrderFilterInput) {
  orders(after: $after, filter: $filter, first: $first) {
    ...OrderListFragment
  }
  orderStatusCounts(filter: $statuslessFilter) {
    status {
      code
      type
      name
    }
    count
  }
}
    ${OrderListFragment}`;

export function useOrdersQuery(options?: Omit<Urql.UseQueryArgs<TypeOrdersQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrdersQuery, TypeOrdersQueryVariables>({ query: OrdersQueryDocument, ...options });
};