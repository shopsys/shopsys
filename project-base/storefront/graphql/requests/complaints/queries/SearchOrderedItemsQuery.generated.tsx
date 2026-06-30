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


export type TypeSearchOrderedItemsQuery = { __typename?: 'Query', orderItemsSearch: { __typename?: 'OrderItemConnection', totalCount: number, edges: Array<{ __typename?: 'OrderItemEdge', node: { __typename?: 'OrderItem', uuid: string, name: string, quantity: number, unit: string | null, totalPrice: { __typename?: 'Price', priceWithVat: string }, order: { __typename?: 'Order', uuid: string, number: string, creationDate: any }, product: { __typename?: 'MainVariant', isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'RegularProduct', isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'Variant', isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null } | null> | null } };


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