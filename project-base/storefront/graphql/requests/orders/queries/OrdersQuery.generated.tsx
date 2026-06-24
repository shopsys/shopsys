// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderListFragment } from '../fragments/OrderListFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrdersQueryVariables = Types.Exact<{
  after?: Types.InputMaybe<Types.Scalars['String']['input']>;
  filter?: Types.InputMaybe<Types.TypeOrderFilterInput>;
  first?: Types.InputMaybe<Types.Scalars['Int']['input']>;
  statuslessFilter?: Types.InputMaybe<Types.TypeOrderFilterInput>;
}>;


export type TypeOrdersQuery = { __typename?: 'Query', orders: { __typename: 'OrderConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'OrderEdge', cursor: string, node: { __typename: 'Order', uuid: string, number: string, creationDate: any, isPaid: boolean, hasExternalPayment: boolean, hasPaymentInProcess: boolean, status: string, note: string | null, productItems: Array<{ __typename: 'OrderItem', quantity: number, product: { __typename: 'MainVariant', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename: 'RegularProduct', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename: 'Variant', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, items: Array<{ __typename?: 'OrderItem', type: Types.TypeOrderItemTypeEnum, payment: { __typename?: 'Payment', name: string, mainImage: { __typename?: 'Image', url: string } | null } | null, transport: { __typename?: 'Transport', name: string, mainImage: { __typename?: 'Image', url: string } | null } | null }> } | null } | null> | null } | null, orderStatusCounts: Array<{ __typename?: 'OrderStatusCount', count: number, status: { __typename?: 'OrderStatus', code: string, type: Types.TypeOrderStatusEnum, name: string } }> };


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