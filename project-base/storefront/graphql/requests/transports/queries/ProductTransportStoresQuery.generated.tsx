// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductTransportStoresFragment } from '../fragments/ProductTransportStoresFragment.generated';
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

export type TypeProductTransportStoresQueryVariables = Exact<{
  transportUuid: string;
  productUuid: string;
  searchText?: string | null | undefined;
  coordinates?: Types.TypeCoordinates | null | undefined;
  first?: number | null | undefined;
  after?: string | null | undefined;
}>;


export type TypeProductTransportStoresQuery = { transport: { __typename: 'Transport', uuid: string, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', expectedDeliveryDate: string | null, slug: string, name: string, description: string | null, latitude: string | null, longitude: string | null, street: string, postcode: string, city: string, distance: number | null, email: string | null, phone: string | null, specialMessage: string | null, identifier: string, openingHours: { status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ date: string, dayOfWeek: number, openingHoursRanges: Array<{ openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null> | null, searchCoordinates: { latitude: number, longitude: number } | null, pageInfo: { hasNextPage: boolean, endCursor: string | null } } | null } | null };


export const ProductTransportStoresQueryDocument = gql`
    query ProductTransportStoresQuery($transportUuid: Uuid!, $productUuid: Uuid!, $searchText: String = null, $coordinates: Coordinates = null, $first: Int, $after: String) {
  transport(uuid: $transportUuid) {
    ...ProductTransportStoresFragment
  }
}
    ${ProductTransportStoresFragment}`;

export function useProductTransportStoresQuery(options: Omit<Urql.UseQueryArgs<TypeProductTransportStoresQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeProductTransportStoresQuery, TypeProductTransportStoresQueryVariables>({ query: ProductTransportStoresQueryDocument, ...options });
};