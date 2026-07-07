// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { StoreDetailFragment } from '../fragments/StoreDetailFragment.generated';
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

export type TypeStoreDetailQueryVariables = Exact<{
  urlSlug?: string | null | undefined;
}>;


export type TypeStoreDetailQuery = { store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, email: string | null, phone: string | null, directions: string | null, specialMessage: string | null, latitude: string | null, longitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ date: string, dayOfWeek: number, openingHoursRanges: Array<{ openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null };


export const StoreDetailQueryDocument = gql`
    query StoreDetailQuery($urlSlug: String) @friendlyUrl {
  store(urlSlug: $urlSlug) {
    ...StoreDetailFragment
  }
}
    ${StoreDetailFragment}`;

export function useStoreDetailQuery(options?: Omit<Urql.UseQueryArgs<TypeStoreDetailQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeStoreDetailQuery, TypeStoreDetailQueryVariables>({ query: StoreDetailQueryDocument, ...options });
};