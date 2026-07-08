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


export type TypeOrdersQuery = (
  { __typename?: 'Query' }
  & { orders: Types.Maybe<(
    { __typename: 'OrderConnection' }
    & Pick<Types.TypeOrderConnection, 'totalCount'>
    & { pageInfo: (
      { __typename: 'PageInfo' }
      & Pick<Types.TypePageInfo, 'hasNextPage' | 'hasPreviousPage' | 'endCursor'>
    ), edges: Types.Maybe<Array<Types.Maybe<(
      { __typename: 'OrderEdge' }
      & Pick<Types.TypeOrderEdge, 'cursor'>
      & { node: Types.Maybe<(
        { __typename: 'Order' }
        & Pick<Types.TypeOrder, 'uuid' | 'number' | 'creationDate' | 'isPaid' | 'hasExternalPayment' | 'hasPaymentInProcess' | 'status' | 'note'>
        & { productItems: Array<(
          { __typename: 'OrderItem' }
          & Pick<Types.TypeOrderItem, 'quantity'>
          & { product: Types.Maybe<(
            { __typename: 'MainVariant' }
            & Pick<Types.TypeMainVariant, 'name' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'link'>
            & { mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )> }
          ) | (
            { __typename: 'RegularProduct' }
            & Pick<Types.TypeRegularProduct, 'name' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'link'>
            & { mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )> }
          ) | (
            { __typename: 'Variant' }
            & Pick<Types.TypeVariant, 'name' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'link'>
            & { mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )> }
          )> }
        )>, totalPrice: (
          { __typename: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ), items: Array<(
          { __typename?: 'OrderItem' }
          & Pick<Types.TypeOrderItem, 'type'>
          & { payment: Types.Maybe<(
            { __typename?: 'Payment' }
            & Pick<Types.TypePayment, 'name'>
            & { mainImage: Types.Maybe<(
              { __typename?: 'Image' }
              & Pick<Types.TypeImage, 'url'>
            )> }
          )>, transport: Types.Maybe<(
            { __typename?: 'Transport' }
            & Pick<Types.TypeTransport, 'name'>
            & { mainImage: Types.Maybe<(
              { __typename?: 'Image' }
              & Pick<Types.TypeImage, 'url'>
            )> }
          )> }
        )> }
      )> }
    )>>> }
  )>, orderStatusCounts: Array<(
    { __typename?: 'OrderStatusCount' }
    & Pick<Types.TypeOrderStatusCount, 'count'>
    & { status: (
      { __typename?: 'OrderStatus' }
      & Pick<Types.TypeOrderStatus, 'code' | 'type' | 'name'>
    ) }
  )> }
);


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