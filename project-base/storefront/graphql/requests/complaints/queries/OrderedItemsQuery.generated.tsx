// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ComplaintOrderedItemFragment } from '../fragments/ComplaintOrderedItemFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderedItemsQueryVariables = Types.Exact<{
  first?: Types.InputMaybe<Types.Scalars['Int']['input']>;
  after?: Types.InputMaybe<Types.Scalars['String']['input']>;
  filter?: Types.InputMaybe<Types.TypeOrderItemsFilterInput>;
}>;


export type TypeOrderedItemsQuery = (
  { __typename?: 'Query' }
  & { orderItems: (
    { __typename: 'OrderItemConnection' }
    & Pick<Types.TypeOrderItemConnection, 'totalCount'>
    & { edges: Types.Maybe<Array<Types.Maybe<(
      { __typename: 'OrderItemEdge' }
      & { node: Types.Maybe<(
        { __typename?: 'OrderItem' }
        & Pick<Types.TypeOrderItem, 'uuid' | 'name' | 'quantity' | 'unit'>
        & { totalPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'currencyCode'>
        ), order: (
          { __typename?: 'Order' }
          & Pick<Types.TypeOrder, 'uuid' | 'number' | 'creationDate'>
        ), product: Types.Maybe<(
          { __typename?: 'MainVariant' }
          & Pick<Types.TypeMainVariant, 'isVisible' | 'slug'>
          & { mainImage: Types.Maybe<(
            { __typename: 'Image' }
            & Pick<Types.TypeImage, 'name' | 'url'>
          )> }
        ) | (
          { __typename?: 'RegularProduct' }
          & Pick<Types.TypeRegularProduct, 'isVisible' | 'slug'>
          & { mainImage: Types.Maybe<(
            { __typename: 'Image' }
            & Pick<Types.TypeImage, 'name' | 'url'>
          )> }
        ) | (
          { __typename?: 'Variant' }
          & Pick<Types.TypeVariant, 'isVisible' | 'slug'>
          & { mainImage: Types.Maybe<(
            { __typename: 'Image' }
            & Pick<Types.TypeImage, 'name' | 'url'>
          )> }
        )> }
      )> }
    )>>> }
  ) }
);


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