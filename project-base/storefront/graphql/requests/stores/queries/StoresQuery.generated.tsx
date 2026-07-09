// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedStoreConnectionFragment } from '../fragments/ListedStoreConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCoordinates = {
  latitude: number;
  longitude: number;
};

/** Status of store opening */
export type TypeStoreOpeningStatusEnum =
  /** Store is currently closed */
  | 'CLOSED'
  /** Store will be closed soon */
  | 'CLOSED_SOON'
  /** Store is currently opened */
  | 'OPEN'
  /** Store will be opened soon */
  | 'OPEN_SOON';

export type TypeStoresQueryVariables = Exact<{
  searchText?: string | null | undefined;
  coordinates?: Types.TypeCoordinates | null | undefined;
  first?: number | null | undefined;
  after?: string | null | undefined;
}>;


export type TypeStoresQuery = { stores: { __typename: 'StoreConnection', searchCoordinates: { latitude: number, longitude: number } | null, pageInfo: { hasNextPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, latitude: string | null, longitude: string | null, street: string, postcode: string, city: string, distance: number | null, email: string | null, phone: string | null, specialMessage: string | null, identifier: string, openingHours: { status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ date: string, dayOfWeek: number, openingHoursRanges: Array<{ openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null> | null } };


export const StoresQueryDocument = gql`
    query StoresQuery($searchText: String = null, $coordinates: Coordinates = null, $first: Int, $after: String) {
  stores(
    searchText: $searchText
    coordinates: $coordinates
    first: $first
    after: $after
  ) {
    ...ListedStoreConnectionFragment
  }
}
    ${ListedStoreConnectionFragment}`;

export function useStoresQuery(options?: Omit<Urql.UseQueryArgs<TypeStoresQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeStoresQuery, TypeStoresQueryVariables>({ query: StoresQueryDocument, ...options });
};