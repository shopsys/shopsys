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


export type TypeOrderedItemsQuery = { __typename?: 'Query', orderItems: { __typename: 'OrderItemConnection', totalCount: number, edges: Array<{ __typename: 'OrderItemEdge', node: { __typename?: 'OrderItem', uuid: string, name: string, quantity: number, unit: string | null, totalPrice: { __typename?: 'Price', priceWithVat: string }, order: { __typename?: 'Order', uuid: string, number: string, creationDate: any }, product: { __typename?: 'MainVariant', isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'RegularProduct', isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'Variant', isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null } | null> | null } };


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