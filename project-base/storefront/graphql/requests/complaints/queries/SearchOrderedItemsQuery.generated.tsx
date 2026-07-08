// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ComplaintOrderedItemFragment } from '../fragments/ComplaintOrderedItemFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSearchOrderedItemsQueryVariables = Types.Exact<{
  first?: Types.InputMaybe<Types.Scalars['Int']['input']>;
  after?: Types.InputMaybe<Types.Scalars['String']['input']>;
  searchInput: Types.TypeSearchInput;
  filter?: Types.InputMaybe<Types.TypeOrderItemsFilterInput>;
}>;


export type TypeSearchOrderedItemsQuery = (
  { __typename?: 'Query' }
  & { orderItemsSearch: (
    { __typename?: 'OrderItemConnection' }
    & Pick<Types.TypeOrderItemConnection, 'totalCount'>
    & { edges: Types.Maybe<Array<Types.Maybe<(
      { __typename?: 'OrderItemEdge' }
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


export const SearchOrderedItemsQueryDocument = gql`
    query SearchOrderedItemsQuery($first: Int, $after: String, $searchInput: SearchInput!, $filter: OrderItemsFilterInput) {
  orderItemsSearch(
    first: $first
    after: $after
    searchInput: $searchInput
    filter: $filter
  ) {
    totalCount
    edges {
      node {
        ...ComplaintOrderedItemFragment
      }
    }
  }
}
    ${ComplaintOrderedItemFragment}`;

export function useSearchOrderedItemsQuery(options: Omit<Urql.UseQueryArgs<TypeSearchOrderedItemsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeSearchOrderedItemsQuery, TypeSearchOrderedItemsQueryVariables>({ query: SearchOrderedItemsQueryDocument, ...options });
};