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

/** Represents search input object */
export type TypeSearchInput = {
  isAutocomplete: boolean;
  /** Ordered list of parameters used in Luigi's Box to ensure same order of parameters in search results */
  parameters?: Array<string> | null | undefined;
  search: string;
  /** Unique identifier of the user who initiated the search in format UUID version 4 (^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[1-8][0-9A-Fa-f]{3}-[ABab89][0-9A-Fa-f]{3}-[0-9A-Fa-f]{12}$/) */
  userIdentifier: string;
};

export type TypeSearchOrderedItemsQueryVariables = Exact<{
  first?: number | null | undefined;
  after?: string | null | undefined;
  searchInput: Types.TypeSearchInput;
  filter?: Types.TypeOrderItemsFilterInput | null | undefined;
}>;


export type TypeSearchOrderedItemsQuery = { orderItemsSearch: { totalCount: number, edges: Array<{ node: { uuid: string, name: string, quantity: number, unit: string | null, totalPrice: { priceWithVat: string }, order: { uuid: string, number: string, creationDate: string }, product:
          | { isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
          | { isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
          | { isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
         | null } | null } | null> | null } };


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