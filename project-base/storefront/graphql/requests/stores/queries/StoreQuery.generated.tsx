// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedStoreFragment } from '../fragments/ListedStoreFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
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

export type TypeStoreQueryVariables = Exact<{
  uuid?: string | null | undefined;
}>;


export type TypeStoreQuery = { store: { __typename: 'Store', slug: string, name: string, description: string | null, latitude: string | null, longitude: string | null, street: string, postcode: string, city: string, distance: number | null, email: string | null, phone: string | null, specialMessage: string | null, identifier: string, openingHours: { status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ date: string, dayOfWeek: number, openingHoursRanges: Array<{ openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null };


export const StoreQueryDocument = gql`
    query StoreQuery($uuid: Uuid) {
  store(uuid: $uuid) {
    ...ListedStoreFragment
  }
}
    ${ListedStoreFragment}`;

export function useStoreQuery(options?: Omit<Urql.UseQueryArgs<TypeStoreQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeStoreQuery, TypeStoreQueryVariables>({ query: StoreQueryDocument, ...options });
};